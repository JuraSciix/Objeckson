<?php

namespace jurasciix\objeckson\test;

class CortegeAdapter {

    public function __invoke(mixed $data): Cortege {
        $instance = new Cortege();
        [$instance->x, $instance->y, $instance->z] = $data;
        return $instance;
    }
}