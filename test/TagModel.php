<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

class TagModel {

    #[JsonProperty]
    public int $date;

    #[JsonProperty]
    public string $name;
}