<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

class AttributeModel {

    #[JsonProperty]
    public int $date;

    #[JsonProperty]
    public string $value;
}