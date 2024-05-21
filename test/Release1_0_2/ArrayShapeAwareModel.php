<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\JsonProperty;
use jurasciix\objeckson\test\Release1_0_0\PairModel;

#[JsonProperty]
class ArrayShapeAwareModel {

    /**
     * @var array{pair: PairModel<string, string>, x?: int, y?: float}
     */
    public array $array;
}