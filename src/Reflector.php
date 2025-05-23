<?php

namespace JuraSciix\Objeckson;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * @internal
 */
class Reflector {
    /**
     * @var PhpDocParserWrapper
     */
    private $phpDocParser;

    public function __construct() {
        $this->phpDocParser = new PhpDocParserWrapper();
    }

    /**
     * @return TypeNode
     */
    public function reflectProperty(ReflectionProperty $property) {
        // PHP-Doc дополняет синтаксические типы.
        // Из соображений простоты, отдаём приоритет PHP-Doc.

        $docComment = $property->getDocComment();
        if ($docComment !== false) {
            $varTagValues = $this->phpDocParser->get($docComment)->getVarTagValues();
            if (count($varTagValues) > 0) {
                if (count($varTagValues) > 1) {
                    // Multiple @var are prohibited!
                    throw new ObjecksonException();
                }
                $typeNode = $varTagValues[0]->type;
                Utils::fixType($typeNode, $property->getDeclaringClass());
                return $typeNode;
            }
        }

        if ($property->hasType()) {
            $typeNode = Utils::fromReflection($property->getType());
            // Заметка: исправлять тип из рефлексии не нужно
            return $typeNode;
        }

        return new IdentifierTypeNode('mixed');
    }

    // todo: reflect methods
}