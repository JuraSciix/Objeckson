<?php

namespace jurasciix\objeckson\test;

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