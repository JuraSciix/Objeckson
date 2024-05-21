<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\JsonProperty;

#[JsonProperty]
class CustomSetterModel {

    public int $foo;

    public int $bar;

    public int $__construct;

    public function __construct() {
        // Конструктор не должен вызываться как сеттер
        echo "__construct";
    }

    public function setFoo(int $foo): void {
        echo "setFoo";
        $this->foo = $foo * 2 + 1;
    }

    public function setBar(int $foo, int $_): void {
        // Не должен вызваться из-за двух параметров
        echo "setBar";
    }

    public function bar(int $bar): void {
        echo "bar";
        $this->bar = (1 - $bar) * 3;
    }

    public function construct(int $construct): void {
        echo "construct";
        $this->__construct = $construct;
    }

    public function __destruct() {
        // Деструктор не должен вызываться как сеттер
        echo "__destruct";
    }
}