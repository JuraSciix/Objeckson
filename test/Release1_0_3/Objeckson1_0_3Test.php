<?php

namespace jurasciix\objeckson\test\Release1_0_3;

use jurasciix\objeckson\Objeckson;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class Objeckson1_0_3Test extends TestCase {

    private Objeckson $objeckson;

    protected function setUp(): void {
        $this->objeckson = new Objeckson();
    }

    public function testExcludedSuccess(): void {
        $data = [
            'foo' => 1
        ];
        $model = $this->objeckson->fromJson($data, ModelWithExcludes::class);
        self::assertInstanceOf(ModelWithExcludes::class, $model, "Model type mismatch");
        self::assertSame(1, $model->foo, "Model foo mismatch");
        self:self::assertFalse((new ReflectionProperty(ModelWithExcludes::class, 'bar'))->isInitialized($model),
            "Model bar initialization mismatch");
    }

//    public function testExcludedError(): void {
//        // todo: Нормализовать вывод ошибок
//    }
}