<?php

namespace JuraSciix\Objeckson\Internal;

use JuraSciix\Objeckson\Context;
use JuraSciix\Objeckson\TreeException;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @internal
 */
class ArrayAdapter {

    public function __construct(
        private readonly TypeNode $keyType,
        private readonly TypeNode $valueType
    ) {}

    public function __invoke(mixed $data, Context $context): array {
        if (!is_array($data)) {
            $type = gettype($data);
            throw new TreeException("Array expected, $type given");
        }

        $array = [];
        foreach ($data as $i => $item) {
            $array[$context->doFromJson($i, $this->keyType)] = $context->doFromJson($item, $this->valueType);
        }

        return $array;
    }
}