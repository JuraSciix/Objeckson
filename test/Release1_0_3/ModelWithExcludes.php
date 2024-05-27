<?php

namespace jurasciix\objeckson\test\Release1_0_3;

use jurasciix\objeckson\Excluded;
use jurasciix\objeckson\JsonProperty;
#[JsonProperty]
class ModelWithExcludes {

    public int $foo;

    #[Excluded]
    public int $bar;
}