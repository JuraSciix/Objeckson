<?php

namespace jurasciix\objeckson;

use AssertionError;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class PhpDoc {

    public static function fromReflection(?ReflectionType $type): ?TypeNode {
        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            $node = new IdentifierTypeNode($type->getName());
            // allowsNull? Почему не NullableType?
            // Спасибо PHP Group за систему типов, будущие изменения
            // которой НЕ просматриваются, :)
            if ($type->allowsNull()) {
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