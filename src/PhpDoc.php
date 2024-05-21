<?php

namespace jurasciix\objeckson;

use AssertionError;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * @internal
 */
final class PhpDoc {

    public static function parseDocComment(string $docComment): PhpDocNode {
        static $lexer, $docParser, $uninitialized = true;
        if ($uninitialized) {
            $lexer = new Lexer();
            $constExprParser = new ConstExprParser();
            $docParser = new PhpDocParser(
                new TypeParser($constExprParser),
                $constExprParser
            );
            unset($constExprParser);
            $uninitialized = false;
        }

        $tokens = $lexer->tokenize($docComment);
        unset($docComment);
        $iterator = new TokenIterator($tokens);
        unset($tokens);
        return $docParser->parse($iterator);
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