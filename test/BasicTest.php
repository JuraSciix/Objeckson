<?php

namespace JuraSciix\Objeckson\Test;

use JuraSciix\Objeckson\Objeckson;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase {

    private Objeckson $objeckson;

    #[Before]
    public function init(): void {
        $this->objeckson = new Objeckson();
    }

    #[Test]
    public function testFromJson(): void {
        $data = [
            'x' => 1,
            'y' => -5,
            'width' => 13,
            'height' => 4
        ];
        $shape = $this->objeckson->fromJson($data, Shape::class);
        self::assertInstanceOf(Shape::class, $shape);
        self::assertSame($shape->x, 1);
        self::assertSame($shape->y, -5);
        self::assertSame($shape->width, 13);
        self::assertSame($shape->height, 4);
    }
}