<?php

namespace jurasciix\objeckson\test\Release1_0_0;

use jurasciix\objeckson\JsonProperty;

enum IssueStatusModel {

    #[JsonProperty('pending')]
    case PENDING;

    #[JsonProperty('open')]
    case OPEN;

    #[JsonProperty('closed')]
    case CLOSED;
}