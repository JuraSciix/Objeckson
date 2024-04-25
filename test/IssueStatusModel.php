<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

enum IssueStatusModel {

    #[JsonProperty('pending')]
    case PENDING;

    #[JsonProperty('open')]
    case OPEN;

    #[JsonProperty('closed')]
    case CLOSED;
}