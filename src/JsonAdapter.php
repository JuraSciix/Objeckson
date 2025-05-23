<?php

namespace JuraSciix\Objeckson;

use Attribute;
use Closure;

#[Attribute]
class JsonAdapter {

    /**
     * @var Closure Экземпляр callable-класса, который отображает массив данных на класс.
     */
    public readonly Closure $adapter;

    public function __construct(callable $adapter) {
        $this->adapter = $adapter(...);
    }
}