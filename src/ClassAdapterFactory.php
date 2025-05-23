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
    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct() {
        $this->phpDocParser = new PhpDocParserWrapper();
        $this->reflector = new Reflector();
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

        $isPropertyClass = true;
        unset($attributes);

        if ($reflection->isEnum()) {
            return $this->enumAdapter($reflection, $isPropertyClass);
        }

        // todo: проверка, что класс не абстрактный.

        /** @var array<string, Property> $properties */
        $properties = [];


        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $attributes = $property->getAttributes(JsonProperty::class);
            if (empty($attributes)) {
                $keys = false;
            } else {
                /** @var JsonProperty $propertyInfo */
                $propertyInfo = $attributes[0]->newInstance();
                $keys = $propertyInfo->keys;
                if (empty($keys)) {
                    $keys = false;
                }
            }


            $typeNode = $this->reflector->reflectProperty($property);

            $required = empty($property->getAttributes(Optional::class));

            $setter = $property->setValue(...);

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