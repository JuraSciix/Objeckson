<?php

namespace jurasciix\objeckson\Internal;

use jurasciix\objeckson\TreeException;
use UnitEnum;

/**
 * @internal
 */
class EnumAdapter {

    /**
     * @param array<string, UnitEnum> $map
     */
    public function __construct(
        private readonly array $map
    ) {}

    public function __invoke(mixed $data): UnitEnum {
        return $this->map[$data] ?? throw new TreeException("No enum case for: $data");
    }
}
