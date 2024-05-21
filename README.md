# Objeckson

__Objeckson__ - это библиотека для отображения динамических ассоциативных структур 
(которые, как правило, хранятся в формате JSON) на объекты PHP.

## Возможности и roadmap
- [x] Десериализовывать массивы
- [x] Отображать на перечисления
- [x] Анализировать обобщенные типы
- [x] Работать с nullable-типами
- [x] Пользовательские сеттеры
- [x] Анализировать array-shapes: array{foo: Foo, bar: Bar}
- [ ] Значения по умолчанию для Readonly свойств
- [ ] Отображать на объекты STD: DateTime, SplFixedArray, etc...
- [ ] Комбинации прямо отображаемых типов (примитивных): string|int|Foo
- [ ] OneOf

## Установка

Требования:
* PHP ≥ 8.1
* Composer

Установка посредством __composer__:
> composer require jurasciix/objeckson:^1.0

## Быстрый туториал

Следующий код демонстрирует инициализацию объекта, предоставляющего доступ к функциям __Objeckson__, 
и десериализацию данных JSON с отображением на объект PHP:

```php
use jurasciix\objeckson\JsonProperty;
use jurasciix\objeckson\Optional;
use jurasciix\objeckson\Objeckson;

#[JsonProperty]
class APIResponse {
    #[Optional]
    public ?APIError $error = null;
    #[Optional]
    public mixed $data = null;
}

#[JsonProperty]
class APIError {
    public int $code;
    public string $description;
}

$responseJSON = <<<JSON
{
    "error": {
        "code": 1,
        "description": "Error description"
    }
}
JSON;

// Инициализируем главный объект 
$objeckson = new Objeckson();

// Десериализовываем и отображаем данные на объект типа APIResponse.
$response = $objeckson->fromJson($responseJSON, APIResponse::class);

// Смотрим, что у нас вышло.
var_dump($response);
```