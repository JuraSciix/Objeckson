<?php

namespace JuraSciix\Objeckson;

use JsonException;

class JsonDecode {

    /**
     * @throws JsonException
     */
    public function __invoke(string $string): mixed {
        return json_decode($string, true, flags: JSON_THROW_ON_ERROR);
    }
}