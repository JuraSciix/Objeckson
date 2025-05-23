<?php

namespace JuraSciix\Objeckson;

use InvalidArgumentException;
use Nette\Utils\Reflection;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use ReflectionClass;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionException;

class ClassAdapterFactory {

    /**
     * @var PhpDocParserWrapper
     */
    private $phpDocParser;

    public function __construct() {
        $this->phpDocParser = new PhpDocParser();
    }

    public function __invoke(IdentifierTypeNode $type, array $templates): callable {
        try {
            $reflection = enum_exists($type->name)
                ? new ReflectionEnum($type->name) : new ReflectionClass($type->name);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                message: "No reflection for class [$type->name]",
                previous: $e
            );
        }

        // Ищем пользовательский адаптер
        $attributes = $reflection->getAttributes(JsonAdapter::class);
        if ($attributes) {
            /** @var JsonAdapter $adapterInfo */
            $adapterInfo = $attributes[0]->newInstance();
            return Utils::wrapCustomAdapter($adapterInfo->adapter);
        }

        $isPropertyClass = false;
        if (!empty($attributes = $reflection->getAttributes(JsonProperty::class))) {
            $propertyInfo = $attributes[0]->newInstance();
            if (!empty($propertyInfo->keys)) {
                throw new TreeException("#[JsonProperty] over above class must not contain values");
            }
            $isPropertyClass = true;
        }

        unset($attributes);

        if ($reflection->isEnum()) {
            return $this->enumAdapter($reflection, $isPropertyClass);
        }

        // todo: проверка, что класс не абстрактный.

        /** @var array<string, Property> $properties */
        $properties = [];

        /** @var array<string, TypeNode> $templateOverlays TemplateName => TypeNode */
        $templateOverlays = [];

        // Связываем переданные обобщенные типы с теми, которые указаны в PHPDoc класса.
        // Если обнаружено несоответствие, то выбрасываем исключение.
        $classDocComment = $reflection->getDocComment();
        if ($classDocComment) {
            $node = $this->phpDocParser->get($classDocComment);
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
                if (!$isPropertyClass) {
                    continue;
                }
                $keys = false;
            } else {
                /** @var JsonProperty $propertyInfo */
                $propertyInfo = $attributes[0]->newInstance();
                $keys = $propertyInfo->keys;
                if (empty($keys)) {
                    $keys = false;
                }
            }


            // Если PHP-doc не указан или отсутствует тег @var, то полагаемся на синтаксический тип.
            // Например: private int $foo
            $propertyDocComment = $property->getDocComment();
            if ($propertyDocComment) {
                $node = $this->phpDocParser->get($propertyDocComment);
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
                $typeNode = Utils::fromReflection($property->getType());
            }

            $required = empty($property->getAttributes(Optional::class));

            $setter = $property->setValue(...);

            foreach (Utils::getSetterNameVariants($property->name) as $variant) {
                if ($reflection->hasMethod($variant)) {
                    $method = $reflection->getMethod($variant);
                    if ($method->isStatic() || $method->isConstructor() || $method->isAbstract()
                        || $method->isDestructor() || $method->isGenerator() || $method->isInternal()
                        || $method->getNumberOfParameters() !== 1) {
                        continue;
                    }
                    // todo: А можно ли как-то покрасивее сделать?
                    $setter = fn ($instance, $value) => $method->getClosure($instance)($value);
                    break;
                }
            }

            // Note: As of PHP 8.1.0, calling the setAccessible()  has no effect; all properties are accessible by default.
            $properties[] = new Property(
                // todo: остальные регистры
                $keys ?: [Utils::toSnakeCase($property->name)],
                $typeNode,
                $setter,
                $required
            );

            unset($typeNode, $propertyInfo, $property);
        }

        return new ClassAdapter($reflection, $properties);
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
        } else if ($node instanceof ArrayShapeNode) {
            foreach ($node->items as $item) {
                $this->fixType($reflection, $item->valueType, $templateOverlays);
            }
        }
    }

    private function enumAdapter(ReflectionEnum $reflection, bool $isProperty): EnumAdapter {
        /** @var JsonProperty $propertyInfo */

        $map = [];

        foreach ($reflection->getCases() as $case) {
            $attributes = $case->getAttributes(JsonProperty::class);
            $keys = [];
            if (empty($attributes)) {
                if (!$isProperty) {
                    continue;
                }
            } else {
                $propertyInfo = $attributes[0]->newInstance();
                $keys = $propertyInfo->keys;
            }
            if (empty($keys)) {
                // Если ключи отсутствуют, значит значение должно находиться в значении кейса
                if ($case instanceof ReflectionEnumBackedCase) {
                    $keys = [$case->getBackingValue()];
                } else {
                    // todo: остальные регистры
                    $keys = [Utils::toSnakeCase($case->name)];
                }
            }
            foreach ($keys as $key) {
                $map[$key] = $case->getValue();
            }
        }
        return new EnumAdapter($map);
    }
}