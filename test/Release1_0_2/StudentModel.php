<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
class StudentModel {

    /**
     * @var RateModel[]
     */
    public array $rates;
}