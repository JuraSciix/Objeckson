<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\Objeckson;
use jurasciix\objeckson\test\Release1_0_0\PairModel;
use jurasciix\objeckson\TreeException;
use PHPUnit\Framework\TestCase;

class Objeckson1_0_2Test extends TestCase {

    private Objeckson $objeckson;

    protected function setUp(): void {
        $this->objeckson = new Objeckson();
    }

    public function testFromJson(): void {
        $data = [
            'rates' => [
                [
                    'subject' => 'Math',
                    'status' => 1
                ]
            ]
        ];
        $model = $this->objeckson->fromJson($data, StudentModel::class);
        self::assertInstanceOf(StudentModel::class, $model, "Model type mismatch");
        self::assertSame(1, sizeof($model->rates), "Model rates count mismatch");
        self::assertInstanceOf(RateModel::class, $model->rates[0], "Model rates[0] type mismatch");
        self::assertSame('Math', $model->rates[0]->subject, "Model rates[0] subject mismatch");
        self::assertSame(StatusModel::PASSED, $model->rates[0]->status, "Model rates[0] status mismatch");
    }

    public function testCustomSetters(): void {
        $data = [
            'foo' => 10,
            'bar' => -6,
            '__construct' => 200,
//            '__destruct' => 50
        ];
        self::expectOutputString("setFoo" . "bar" . "construct");
        $model = $this->objeckson->fromJson($data, CustomSetterModel::class);
        self::expectOutputString("setFoo" . "bar" . "construct" . "__destruct");
        self::assertInstanceOf(CustomSetterModel::class, $model, "Model type mismatch");
        self::assertSame(10 * 2 + 1, $model->foo, "Model foo mismatch");
        self::assertSame((1 - -6) * 3, $model->bar, "Model bar mismatch");
        self::assertSame(200, $model->__construct, "Model __construct mismatch");
    }

    public function testArrayShapesWithGenerics(): void {
        // Тестируем сразу array-shapes с обобщенными типами
        $data = [
            'array' => [
                'pair' => [
                    'key' => 'foo',
                    'value' => 'bar'
                ],
                'x' => 10
            ]
        ];
        $model = $this->objeckson->fromJson($data, ArrayShapeAwareModel::class);
        self::assertInstanceOf(ArrayShapeAwareModel::class, $model, "Model type mismatch");
        self::assertInstanceOf(PairModel::class, $model->array['pair'], "Model array pair mismatch");
        self::assertSame('foo', $model->array['pair']->key, "Model array pair key mismatch");
        self::assertSame('bar', $model->array['pair']->value, "Model array pair value mismatch");
        self::assertSame(10, $model->array['x'], "Model array x mismatch");
        self::assertNotContains('y', $model->array, "Model array y containing mismatch");
    }

    public function testArrayShapeWithoutRequiredShape(): void {
        // Тестируем сразу array-shapes с обобщенными типами
        $data = [
            'array' => [
                'x' => 10
            ]
        ];
        self::expectException(TreeException::class);
        self::expectExceptionMessage("Unable to map property \"array\"");
        $this->objeckson->fromJson($data, ArrayShapeAwareModel::class);
    }
}