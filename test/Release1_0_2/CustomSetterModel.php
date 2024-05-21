<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
class CustomSetterModel {

    public int $foo;

    public int $bar;

    public function setFoo(int $foo): void {
        echo "setFoo";
        $this->foo = $foo * 2 + 1;
    }

    public function bar(int $bar): void {
        echo "bar";
        $this->bar = (1 - $bar) * 3;
    }
}