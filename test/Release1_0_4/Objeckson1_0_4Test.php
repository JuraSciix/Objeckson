<?php

namespace jurasciix\objeckson\test\Release1_0_4;

use jurasciix\objeckson\Objeckson;
use PHPUnit\Framework\TestCase;

class Objeckson1_0_4Test extends TestCase {

    public function testExcludedSuccess(): void {
        // Проверяем, что ::get() инициализировался
        $this->assertEquals(Objeckson::get(), Objeckson::get());
    }
}