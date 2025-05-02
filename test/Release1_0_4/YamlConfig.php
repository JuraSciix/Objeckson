<?php

namespace jurasciix\objeckson\test\Release1_0_4;

use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
class YamlConfig {

    /**
     * @var array<string, Component>
     */
    public array $components;
}