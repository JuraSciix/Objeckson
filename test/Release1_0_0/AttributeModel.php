<?php

namespace jurasciix\objeckson\test\Release1_0_0;

use jurasciix\objeckson\JsonProperty;

class AttributeModel {

    #[JsonProperty]
    public int $date;

    #[JsonProperty]
    public string $value;
}