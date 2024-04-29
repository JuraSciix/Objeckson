<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

enum RateModel: string {

    #[JsonProperty]
    case MINIMAL = 'min';

    #[JsonProperty]
    case MEDIUM = 'med';

    #[JsonProperty]
    case PREMIUM = 'prm';
}