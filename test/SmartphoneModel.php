<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;

class SmartphoneModel {

    #[JsonProperty]
    public ColorModel $bodyColor;
}