<?php

namespace jurasciix\objeckson\test\Release1_0_4;

use jurasciix\objeckson\Objeckson;
use jurasciix\objeckson\ObjecksonException;
use PHPUnit\Framework\TestCase;

class Objeckson1_0_4Test extends TestCase {

    public function testObjecksonGet(): void {
        // Проверяем, что ::get() инициализировался
        $this->assertEquals(Objeckson::get(), Objeckson::get());
    }

    public function testGenericArrays(): void {
        $data = [
            'components' => [
                'foo' => [
                    'id' => 0xABC0123
                ],
                'bar' => [
                    'id' => 0xBEC2236
                ]
            ]
        ];

        $config = Objeckson::get()->fromJson($data, YamlConfig::class);
        $this->assertInstanceOf(YamlConfig::class, $config, "YamlConfig instance mismatch");
        $this->assertSame(2, sizeof($config->components), "YamlConfig components count mismatch");
        $this->assertArrayHasKey("foo", $config->components, "YamlConfig has no 'foo' component");
        $this->assertInstanceOf(Component::class, $config->components['foo'], "YamlConfig 'foo' component instance mismatch");
        $this->assertSame(0xABC0123, $config->components['foo']->id, "YamlConfig 'foo' component id mismatch");
        $this->assertArrayHasKey("bar", $config->components, "YamlConfig has no 'bar' component");
        $this->assertInstanceOf(Component::class, $config->components['bar'], "YamlConfig 'bar' component instance mismatch");
        $this->assertSame(0xBEC2236, $config->components['bar']->id, "YamlConfig 'bar' component id mismatch");
    }

    public function testCustomAdapterException(): void {
        $this->expectException(ObjecksonException::class);
        $this->expectExceptionMessage("An exception occurred in custom adapter");
        Objeckson::get()->fromJson([], MyModel::class);
    }
}