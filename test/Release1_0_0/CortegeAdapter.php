<?php

namespace jurasciix\objeckson\test\Release1_0_0;

class CortegeAdapter {

    public function __invoke(mixed $data): Cortege {
        $instance = new Cortege();
        [$instance->x, $instance->y, $instance->z] = $data;
        return $instance;
    }
}