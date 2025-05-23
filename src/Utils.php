<?php

namespace JuraSciix\Objeckson;

use AssertionError;
use Exception;
use Iterator;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * @internal
 */
final class Utils {

    /**
     * Конвертирует регистр текста из camelCase/PascalCase/UPPER_SNAKE_CASE в snake_case.
     */
    public static function toSnakeCase(string $text): string {
        return strtolower(preg_replace('/(?<=\p{Ll})\p{Lu}+\p{Ll}*/', '_$0', $text));
    }

    /**
     * Возвращает итератор вариантов для названий сеттера для поля $fieldName.
     */
    public static function getSetterNameVariants(string $fieldName): Iterator {
        // todo: Детальная конвертация регистров. Пример: __block => Block
        $trimmedFieldName = trim($fieldName, '_');
        yield "set" . ucfirst($trimmedFieldName);
        yield $trimmedFieldName;
        yield $fieldName;
    }

    /**
     * Проверяет, чтобы `$node` был типом массива.
     *
     * @return bool
     */
    public static function isTypeNodeArray(TypeNode $node) {
        if ($node instanceof ArrayShapeNode) {
            return true;
        }

        // Если тип определен с обобщенными типами, то PHPStan
        // воспринимает 'array' за идентификатор...
        // Пример: array<int, string>
        // Результат: GenericTypeNode(IdentifierTypeNode('array'), [...])
        if (($node instanceof IdentifierTypeNode) && strcasecmp($node->name, 'array') === 0) {
            return true;
        }

        return false;
    }

    public static function wrapCustomAdapter(callable $function) {
        return function (...$args) use ($function) {
            try {
                return call_user_func_array($function, $args);
            } catch (Exception $e) {
                throw new ObjecksonException(
                    message: "An exception occurred in custom adapter",
                    previous: $e
                );
            }
        };
    }

    public static function fromReflection(?ReflectionType $type): ?TypeNode {
        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            $node = new IdentifierTypeNode($type->getName());
            // allowsNull? Почему не ReflectionNullableType?
            // Спасибо PHP Group за систему типов, будущие изменения
            // которой НЕ просматриваются, :)
            if ($type->allowsNull() &&
                // Тип null не должен оборачиваться в NullableTypeNode.
                $type->getName() !== 'null') {
                $node = new NullableTypeNode($node);
            }
            return $node;
        }

        if ($type instanceof ReflectionUnionType) {
            $types = [];
            foreach ($type->getTypes() as $t) {
                $types[] = self::fromReflection($t);
            }
            return new IntersectionTypeNode($types);
        }

        if ($type instanceof ReflectionIntersectionType) {
            $types = [];
            foreach ($type->getTypes() as $t) {
                $types[] = self::fromReflection($t);
            }
            return new IntersectionTypeNode($types);
        }

        throw new AssertionError(get_class($type));
    }
}