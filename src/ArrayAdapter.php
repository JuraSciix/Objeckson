<?php

namespace jurasciix\objeckson;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * @internal
 */
class ArrayAdapter {

    public function __construct(
        private readonly TypeNode $componentType
    ) {}

    public function __invoke(mixed $data, AdapterContext $context): array {
        if (!is_array($data)) {
            $type = gettype($data);
            throw new TreeException("Array expected, $type given");
        }

        $array = [];
        foreach ($data as $i => $item) {
            $array[$i] = $context->fromJson($item, $this->componentType);
        }

        return $array;
    }
}