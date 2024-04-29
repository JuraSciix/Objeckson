<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\Objeckson;
use PHPUnit\Framework\TestCase;

class ObjecksonTest extends TestCase {

    private Objeckson $objeckson;

    protected function setUp(): void {
        $this->objeckson = new Objeckson();
    }

    public function testFromJsonSimple(): void {
        $data = [
            'date' => 1713935188,
            'value' => 'Hello'
        ];
        $model = $this->objeckson->fromJson($data, AttributeModel::class);
        self::assertInstanceOf(AttributeModel::class, $model, "Model type mismatch");
        self::assertEquals(1713935188, $model->date, "Model field 'date' mismatch");
        self::assertEquals('Hello', $model->value, "Model field 'value' mismatch");
    }

    public function testArrayFromJson(): void {
        $data = [
            [
                'date' => 1713935188,
                'value' => 'Hello'
            ],
            [
                'date' => 1713936207,
                'value' => 'Bye'
            ]
        ];
        /** @var AttributeModel[] $models */
        $models = $this->objeckson->arrayFromJson($data, AttributeModel::class);
        self::assertIsArray($models, "Models is not array");
        self::assertEquals(2, sizeof($models), "Models size mismatch");
        self::assertInstanceOf(AttributeModel::class, $models[0], "Models 0 type mismatch");
        self::assertEquals(1713935188, $models[0]->date, "Models 0 field date mismatch");
        self::assertEquals('Hello', $models[0]->value, "Models 0 field date mismatch");
        self::assertInstanceOf(AttributeModel::class, $models[1], "Models 1 type mismatch");
        self::assertEquals(1713936207, $models[1]->date, "Models 1 field date mismatch");
        self::assertEquals('Bye', $models[1]->value, "Models 1 field date mismatch");
    }

    public function testFromJsonWithGenerics(): void {
        $data = [
            'key' => [
                'date' => 1713935188,
                'name' => 'My tag'
            ],
            'value' => [
                'date' => 1713936207,
                'value' => 'My attribute'
            ]
        ];
        /** @var PairModel<TagModel, AttributeModel> $model */
        $model = $this->objeckson->fromJsonWithGenerics($data, PairModel::class, TagModel::class, AttributeModel::class);
        self::assertInstanceOf(PairModel::class, $model, "Model type mismatch");
        self::assertInstanceOf(TagModel::class, $model->key, "Model key type mismatch");
        self::assertEquals('My tag', $model->key->name, "Model key field 'name' mismatch");
        self::assertEquals(1713935188, $model->key->date, "Model key field 'date' mismatch");
        self::assertInstanceOf(AttributeModel::class, $model->value, "Model key type mismatch");
        self::assertEquals(1713936207, $model->value->date, "Model value field 'date' mismatch");
        self::assertEquals('My attribute', $model->value->value, "Model value field 'name' mismatch");
    }

    public function testFromJsonAdvanced(): void {
        $data = [
            'description' => 'This is my bookmark',
            'tags' => [
                'tag1',
                'tag2'
            ],
            'attributes' => [
                [
                    'key' => 'created',
                    'value' => [
                        'date' => 1713936207,
                        'value' => 1713934631
                    ]
                ]
            ]
        ];
        $model = $this->objeckson->fromJson($data, BookmarkModel::class);
        self::assertInstanceOf(BookmarkModel::class, $model, "Model type mismatch");
        self::assertEquals('This is my bookmark', $model->description, "Model field 'description' mismatch");
        // Тип поля tags указан синтаксически. PHP отвечает за то, чтобы тип значения был соответствующим,
        // поэтому писать соответствующую проверку не нужно.
        self::assertEquals(2, sizeof($model->tags), "Model field 'tags' size mismatch");
        self::assertEquals('tag1', $model->tags[0], "Model field 'tags'[0] mismatch");
        self::assertEquals('tag2', $model->tags[1], "Model field 'tags'[1] mismatch");
        self::assertEquals(1, sizeof($model->attributes), "Model field 'attributes' size mismatch");
        self::assertInstanceOf(PairModel::class, $model->attributes[0], "Model field 'attributes'[0] type mismatch");
        self::assertEquals('created', $model->attributes[0]->key, "Model field 'attributes' 0 key mismatch");
        self::assertInstanceOf(AttributeModel::class, $model->attributes[0]->value, "Model field 'attributes'[0] type mismatch");
        self::assertEquals(1713936207, $model->attributes[0]->value->date, "Model field 'attributes'[0] date mismatch");
        self::assertEquals(1713934631, $model->attributes[0]->value->value, "Model field 'attributes'[0] value mismatch");
    }

    public function testCustomAdapters(): void {
        $data = [51, 67, 38];
        $model = $this->objeckson->fromJson($data, Cortege::class);
        self::assertInstanceOf(Cortege::class, $model, "Model type mismatch");
        self::assertEquals(51, $model->x, "Model field 'x' mismatch");
        self::assertEquals(67, $model->y, "Model field 'y' mismatch");
        self::assertEquals(38, $model->z, "Model field 'z' mismatch");
    }

    public function testEnums(): void {
        $data = [
            'title' => 'MY ISSUE!',
            'status' => 'closed'
        ];
        $model = $this->objeckson->fromJson($data, IssueModel::class);
        self::assertInstanceOf(IssueModel::class, $model, "Model type mismatch");
        self::assertEquals('MY ISSUE!', $model->title, "Model field 'title' mismatch");
        self::assertEquals('', $model->comment, "Model field 'comment' mismatch");
        // Тип поля tags указан синтаксически. PHP отвечает за то, чтобы тип значения был соответствующим,
        // поэтому писать соответствующую проверку не нужно.
        self::assertEquals(IssueStatusModel::CLOSED, $model->status, "Model field 'status' mismatch");
    }

    public function testEnumBackedValueAsImplicitKey(): void {
        $data = [
            'key' => 'rate',
            'value' => 'med'
        ];
        /** @var PairModel<string, RateModel> $model */
        $model = $this->objeckson->fromJsonWithGenerics($data, PairModel::class, 'string', RateModel::class);
        self::assertInstanceOf(PairModel::class, $model, "Model type mismatch");
        self::assertEquals('rate', $model->key, "Model key mismatch");
        self::assertEquals(RateModel::MEDIUM, $model->value, "Model value mismatch");
    }

    public function testMemberNameAsImplicitKey(): void {
        $data = [
            'body_color' => 'bright_red'
        ];
        $model = $this->objeckson->fromJson($data, SmartphoneModel::class);
        self::assertInstanceOf(SmartphoneModel::class, $model, "Model type mismatch");
        self::assertEquals(ColorModel::BRIGHT_RED, $model->bodyColor, "Model bodyColor mismatch");
    }
}