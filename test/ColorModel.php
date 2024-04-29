<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

enum ColorModel {

    #[JsonProperty]
    case BRIGHT_RED;

    #[JsonProperty]
    case BLACK;
}