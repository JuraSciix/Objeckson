<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
enum StatusModel: int {

    case PASSED = 1;
    case NOT_PASSED = 0;
}