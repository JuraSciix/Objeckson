<?php

namespace jurasciix\objeckson\Internal;

use jurasciix\objeckson\AdapterContext;
use jurasciix\objeckson\TreeException;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;

/**
 * @internal
 */
class ArrayShapeAdapter {

    /**
     * @param array<string, ArrayShapeItemNode> $shapes
     */
    public function __construct(
        private readonly array $shapes
    ) {}

    public function __invoke(mixed $data, AdapterContext $context): array {
        if (!is_array($data)) {
            $type = gettype($data);
            throw new TreeException("Array expected, $type given");
        }

        $result = [];
        foreach ($this->shapes as $shape) {
            $key = $shape->keyName->name;
            if (array_key_exists($key, $data)) {
                $result[$key] = $context->fromJson($data[$key], $shape->valueType);
            } else {
                if (!$shape->optional) {
                    throw new TreeException("Required shape key \"$key\" is missing");
                }
            }
            unset($data[$key]);
        }

        // В $data остались только не проработанные ключ
        foreach ($data as $key => $value) {
            $result[$key] = $this->processUnrecognizedShapeType($key, $value);
        }

        return $result;
    }

    protected function processUnrecognizedShapeType(string $key, mixed $value): mixed {
        throw new TreeException("Unrecognized shape key \"$key\"");
    }
}