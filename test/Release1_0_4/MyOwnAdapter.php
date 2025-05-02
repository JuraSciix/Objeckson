<?php

namespace jurasciix\objeckson\test\Release1_0_4;

use RuntimeException;

class MyOwnAdapter {

    public function __invoke(array $data) {
        throw new RuntimeException("Hello");
    }
}