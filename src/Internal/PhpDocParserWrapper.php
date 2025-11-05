<?php

namespace jurasciix\objeckson\Internal;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * @internal
 */
final class PhpDocParserWrapper {
    private readonly Lexer $lexer;
    private readonly TypeParser $typeParser;
    private readonly PhpDocParser $phpDocParser;

    public function __construct() {
        $constExprParser = new ConstExprParser();
        $this->lexer = new Lexer();
        $this->typeParser = new TypeParser($constExprParser);
        $this->phpDocParser = new PhpDocParser($this->typeParser, $constExprParser);
    }

    /**
     * @return PhpDocNode
     */
    public function parseDocument(string $document) {
        $tokens = $this->lexer->tokenize($document);
        return $this->phpDocParser->parse(new TokenIterator($tokens));
    }

    /**
     * @return TypeNode
     */
    public function parseType(string $type) {
        $tokens = $this->lexer->tokenize($type);
        return $this->typeParser->parse(new TokenIterator($tokens));
    }
}