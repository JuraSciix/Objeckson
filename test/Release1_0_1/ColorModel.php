<?php

namespace jurasciix\objeckson\test\Release1_0_1;

use jurasciix\objeckson\JsonProperty;

enum ColorModel {

    #[JsonProperty]
    case BRIGHT_RED;

    #[JsonProperty]
    case BLACK;
}