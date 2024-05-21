<?php

namespace jurasciix\objeckson\test\Release1_0_0;

use jurasciix\objeckson\JsonProperty;

/**
 * @template TKey
 * @template TValue
 */
class PairModel {

    /**
     * @var TKey
     */
    #[JsonProperty]
    public mixed $key;

    /**
     * @var TValue
     */
    #[JsonProperty]
    public mixed $value;
}