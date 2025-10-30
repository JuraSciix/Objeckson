<?php

namespace jurasciix\objeckson\Internal;

use Closure;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @internal
 */
class Property {

    public function __construct(
        public readonly array    $keys,
        public readonly TypeNode $type,
        public readonly Closure  $accessor,
        public readonly bool     $required
    ) {}
}