<?php

namespace jurasciix\objeckson;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS_CONSTANT)]
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
                    // Поскольку $aliases[$j] идентично $alias, вместо него можно упомянуть $alias.
                    throw new InvalidArgumentException("Duplicated key: $key");
                }
            }
        }
    }
}