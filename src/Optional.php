<?php

namespace jurasciix\objeckson;

use Attribute;

/**
 * Указывает, что свойство может отсутствовать в структуре JSON.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Optional {

}