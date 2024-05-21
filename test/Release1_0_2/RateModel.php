<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
class RateModel {

    public string $subject;

    public StatusModel $status;
}