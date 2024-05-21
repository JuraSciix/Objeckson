<?php

namespace jurasciix\objeckson\test\Release1_0_1;

use jurasciix\objeckson\JsonProperty;

enum RateModel: string {

    #[JsonProperty]
    case MINIMAL = 'min';

    #[JsonProperty]
    case MEDIUM = 'med';

    #[JsonProperty]
    case PREMIUM = 'prm';
}