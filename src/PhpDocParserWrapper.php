<?php

namespace JuraSciix\Objeckson;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

class PhpDocParserWrapper {
    private readonly Lexer $lexer;
    private readonly PhpDocParser $phpDocParser;

    public function __construct() {
        $constExprParser = new ConstExprParser();
        $this->lexer = new Lexer();
        $this->phpDocParser = new PhpDocParser(new TypeParser($constExprParser), $constExprParser);
    }

    /**
     * @return PhpDocNode
     */
    public function get(string $document) {
        $tokens = $this->lexer->tokenize($document);
        return $this->phpDocParser->parse(new TokenIterator($tokens));
    }
}