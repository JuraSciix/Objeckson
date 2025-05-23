<?php

namespace JuraSciix\Objeckson;

use AssertionError;
use Exception;
use Iterator;
use Nette\Utils\Reflection;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use ReflectionClass;
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

    public static function isTypeNodeNull(TypeNode $node) {
        return $node instanceof IdentifierTypeNode && strcasecmp($node->name, 'null') === 0;
    }

    public static function fixType(TypeNode &$node, ReflectionClass $reflection): void {
        if ($node instanceof UnionTypeNode) {
            // Преобразуем X|null в ?X
            $nullable = false;
            foreach ($node->types as $i => &$type) {
                if (self::isTypeNodeNull($type)) {
                    // Нетрезвый программист может дважды записать null.
                    // Удаляем null.
                    unset($node->types[$i]);
                    $nullable = true;
                } else {
                    self::fixType($type, $reflection);
                }
            }
            if (count($node->types) == 1) {
                $node = $node->types[0];
            }
            if ($nullable) {
                $node = new NullableTypeNode($node);
            }
        } else if ($node instanceof IdentifierTypeNode) {
            if (!class_exists($node->name, false)) {
                $node->name = Reflection::expandClassName($node->name, $reflection);
            }
        } else if ($node instanceof ArrayShapeNode) {
            foreach ($node->items as $item) {
                self::fixType($item->valueType, $reflection);
            }
        } else if ($node instanceof ArrayTypeNode || $node instanceof NullableTypeNode) {
            self::fixType($node->type, $reflection);
        }

        // То, что не поддерживается (generics, etc) - не исправляем.
    }


    /**
     * @return TypeNode|null
     */
    public static function fromReflection(?ReflectionType $type) {
        if (is_null($type)) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            $node = new IdentifierTypeNode($type->getName());
            // allowsNull? Почему не ReflectionNullableType?
            // Спасибо PHP Group за систему типов, будущие изменения
            // которой НЕ просматриваются, :)
            if ($type->allowsNull() &&
                // Тип null не должен оборачиваться в NullableTypeNode.
                // Заметка: Рефлексия всегда возвращает эту строку в нижнем регистре
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