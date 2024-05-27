<?php

namespace jurasciix\objeckson\test\Release1_0_3;

use jurasciix\objeckson\Excluded;
use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
class InvalidModelWithExcludes {

    public string $foo;

    #[JsonProperty]
    #[Excluded]
    public string $bar;
}