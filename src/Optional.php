<?php

namespace JuraSciix\Objeckson;

use Attribute;

/**
 * Указывает, что свойство может отсутствовать в структуре JSON.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Optional {

}