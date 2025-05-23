<?php

namespace JuraSciix\Objeckson;

use Attribute;
use InvalidArgumentException;

/**
 * Указывает, что поле или класс являются частью модели JSON.
 *
 * JsonProperty можно повесить на свойство класса или на сам класс.
 * Если повесить JsonProperty на класс, то он будет относиться к каждому свойству класса.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_CLASS)]
class JsonProperty {

    /**
     * @var string[] JSON object keys.
     */
    public readonly array $keys;

    public function __construct(string ...$keys) {
        $this->checkUniqueness($keys);
        $this->keys = $keys;
    }

    private static function checkUniqueness(array $keys): void {
        for ($i = 0; $i < sizeof($keys); $i++) {
            $key = $keys[$i];
            for ($j = $i + 1; $j < sizeof($keys); $j++) {
                if ($keys[$j] === $key) {
                    throw new InvalidArgumentException("Duplicated key: $key");
                }
            }
        }
    }
}