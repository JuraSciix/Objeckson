<?php

namespace jurasciix\objeckson;

use InvalidArgumentException;
use Nette\Utils\Reflection;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionException;

class AdaptTreeFactory {

    private readonly Lexer $lexer;

    private readonly PhpDocParser $docParser;

    public function __construct() {
        $this->lexer = new Lexer();
        $constExprParser = new ConstExprParser();
        $this->docParser = new PhpDocParser(
            new TypeParser($constExprParser),
            $constExprParser
        );
    }

    public function __invoke(TypeNode $type): callable {
        if ($type instanceof IdentifierTypeNode) {
            $class = $type->name;
            $templates = [];
        } else if ($type instanceof GenericTypeNode) {
            $class = $type->type->name;
            $templates = $type->genericTypes;
        } else {
            throw new TreeException("Only identifiers and generic types are supported");
        }

        try {
            $reflection = enum_exists($class)
                ? new ReflectionEnum($class) : new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                message: "No reflection for class [$class]",
                previous: $e
            );
        }
        unset($class);

        // Ищем пользовательский адаптер
        $attributes = $reflection->getAttributes(JsonAdapter::class);
        if ($attributes) {
            /** @var JsonAdapter $adapterInfo */
            $adapterInfo = $attributes[0]->newInstance();
            return $adapterInfo->adapter;
        }
        unset($attributes);

        if ($reflection->isEnum()) {
            return $this->enumAdapter($reflection);
        }

        /** @var array<string, Property> $properties */
        $properties = [];

        /** @var array<string, TypeNode> $templateOverlays TemplateName => TypeNode */
        $templateOverlays = [];

        // Связываем переданные обобщенные типы с теми, которые указаны в PHPDoc класса.
        // Если обнаружено несоответствие, то выбрасываем исключение.
        $classDocComment = $reflection->getDocComment();
        if ($classDocComment) {
            $node = $this->parseDoc($classDocComment);
            $templateNodes = $node->getTemplateTagValues();
            unset($node);
            foreach ($templateNodes as $i => $templateNode) {
                if (!isset($templates[$i])) {
                    break;
                }
                $templateOverlays[$templateNode->name] = $templates[$i];
            }
            unset($templateNodes);
        }
        if (sizeof($templateOverlays) !== sizeof($templates)) {
            $declared = sizeof($templateOverlays);
            $given = sizeof($templates);
            throw new TreeException("Templates count mismatch. Declared: $declared, given: $given");
        }
        unset($templates);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $attributes = $property->getAttributes(JsonProperty::class);
            if (empty($attributes)) {
                continue;
            }

            /** @var JsonProperty $propertyInfo */
            $propertyInfo = $attributes[0]->newInstance();

            // Если PHP-doc не указан или отсутствует тег @var, то полагаемся на синтаксический тип.
            // Например: private int $foo
            $propertyDocComment = $property->getDocComment();
            if ($propertyDocComment) {
                $node = $this->parseDoc($propertyDocComment);
                $tags = $node->getVarTagValues();
                unset($node);
                if ($tags) {
                    if (sizeof($tags) > 1) {
                        throw new TreeException("@var must be single");
                    }
                    $docType = $tags[0]->type;
                    unset($tags);
                    // Заменяем обобщенные типы со ссылок на явные.
                    $this->fixType($reflection, $docType, $templateOverlays);
                    $typeNode = $docType;
                    unset($docType);
                } else {
                    unset($tags);
                }
            }

            if (!isset($typeNode)) {
                // Объединенные (A|B) и пересеченные (A&B) пока не поддерживаются.
                $typeNode = PhpDoc::fromReflection($property->getType());
            }

            $required = empty($property->getAttributes(Optional::class));

            // Note: As of PHP 8.1.0, calling the setAccessible()  has no effect; all properties are accessible by default.
            $properties[] = new Property(
                // todo: остальные регистры
                $propertyInfo->keys ?: [Utils::toSnakeCase($property->name)],
                $typeNode,
                $property->setValue(...),
                $required
            );

            unset($typeNode, $propertyInfo, $property);
        }

        return new AdaptTree($reflection, $properties);
    }

    private function parseDoc(string $docComment): PhpDocNode {
        $tokens = $this->lexer->tokenize($docComment);
        $iterator = new TokenIterator($tokens);
        unset($tokens);
        return $this->docParser->parse($iterator);
    }

    private function fixType(ReflectionClass $reflection, TypeNode &$node, array $templateOverlays): void {
        if ($node instanceof UnionTypeNode) {
            // X|null => ?X

            // Нетрезвый программист может дважды записать null.
            // Удаляем null.
            $nullable = false;
            foreach ($node->types as $i => &$type) {
                if ($type instanceof IdentifierTypeNode && strcasecmp($type->name, 'null') === 0) {
                    unset($node->types[$i]);
                    $nullable = true;
                } else {
                    $this->fixType($reflection, $type, $templateOverlays);
                }
            }
            if ($nullable) {
                $node = new NullableTypeNode($node);
            }
        } else if ($node instanceof IdentifierTypeNode) {
            if (isset($templateOverlays[$node->name])) {
                $node = $templateOverlays[$node->name];
                return;
            }
            if (!class_exists($node->name, false)) {
                $node->name = Reflection::expandClassName($node->name, $reflection);
            }
        } else if ($node instanceof GenericTypeNode) {
            $this->fixType($reflection, $node->type, $templateOverlays);
            foreach ($node->genericTypes as &$type) {
                $this->fixType($reflection, $type, $templateOverlays);
            }
        } else if ($node instanceof ArrayTypeNode) {
            $this->fixType($reflection, $node->type, $templateOverlays);
        }
        // todo: array shapes: array{x: Foo, y: Bar}
    }

    private function enumAdapter(ReflectionEnum $reflection): EnumAdapter {
        $map = [];
        foreach ($reflection->getCases() as $case) {
            $attributes = $case->getAttributes(JsonProperty::class);
            if (empty($attributes)) {
                continue;
            }
            /** @var JsonProperty $propertyInfo */
            $propertyInfo = $attributes[0]->newInstance();
            if (empty($propertyInfo->keys)) {
                // Если ключи отсутствуют, значит значение должно находиться в значении кейса
                if ($case instanceof ReflectionEnumBackedCase) {
                    $keys = [$case->getBackingValue()];
                } else {
                    // todo: остальные регистры
                    $keys = [Utils::toSnakeCase($case->name)];
                }
            } else {
                $keys = $propertyInfo->keys;
            }
            foreach ($keys as $key) {
                $map[$key] = $case->getValue();
            }
        }
        return new EnumAdapter($map);
    }
}