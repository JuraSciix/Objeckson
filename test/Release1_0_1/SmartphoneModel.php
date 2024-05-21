<?php

namespace jurasciix\objeckson\test\Release1_0_1;

use jurasciix\objeckson\JsonProperty;

class SmartphoneModel {

    #[JsonProperty]
    public ColorModel $bodyColor;
}