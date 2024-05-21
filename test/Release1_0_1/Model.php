<?php

namespace jurasciix\objeckson\test\Release1_0_1;

use jurasciix\objeckson\JsonProperty;
use jurasciix\objeckson\test\Release1_0_0\PairModel;

class Model {

    /**
     * @var string|null|null
     */
    #[JsonProperty]
    public ?string $foo;

    /**
     * @var PairModel<string, string>|null
     */
    #[JsonProperty]
    public ?PairModel $pair;
}