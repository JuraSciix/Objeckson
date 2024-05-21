<?php

namespace jurasciix\objeckson\test\Release1_0_2;

use jurasciix\objeckson\Objeckson;
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
}