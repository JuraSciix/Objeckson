<?php

namespace jurasciix\objeckson\test\Release1_0_1;

use jurasciix\objeckson\Objeckson;
use jurasciix\objeckson\test\Release1_0_0\PairModel;
use PHPUnit\Framework\TestCase;

class Objeckson1_0_1Test extends TestCase {

    private Objeckson $objeckson;

    protected function setUp(): void {
        $this->objeckson = new Objeckson();
    }

    public function testEnumBackedValueAsImplicitKey(): void {
        $data = [
            'key' => 'rate',
            'value' => 'med'
        ];
        /** @var PairModel<string, RateModel> $model */
        $model = $this->objeckson->fromJsonWithGenerics($data, PairModel::class, 'string', RateModel::class);
        self::assertInstanceOf(PairModel::class, $model, "Model type mismatch");
        self::assertSame('rate', $model->key, "Model key mismatch");
        self::assertSame(RateModel::MEDIUM, $model->value, "Model value mismatch");
    }

    public function testMemberNameAsImplicitKey(): void {
        $data = [
            'body_color' => 'bright_red'
        ];
        $model = $this->objeckson->fromJson($data, SmartphoneModel::class);
        self::assertInstanceOf(SmartphoneModel::class, $model, "Model type mismatch");
        self::assertSame(ColorModel::BRIGHT_RED, $model->bodyColor, "Model bodyColor mismatch");
    }

    public function testNullable(): void {
        $data = [
            'foo' => null,
            'pair' => null
        ];
        $model = $this->objeckson->fromJson($data, Model::class);
        self::assertInstanceOf(Model::class, $model, "Model type mismatch");
        self::assertSame(null, $model->foo, "Model field foo mismatch");
        self::assertSame(null, $model->pair, "Model field pair mismatch");
    }
}