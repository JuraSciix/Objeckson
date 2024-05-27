<?php

namespace jurasciix\objeckson;

use Attribute;

/**
 * Этот атрибут указывает, что поле НЕ является частью модели JSON.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Excluded {

}