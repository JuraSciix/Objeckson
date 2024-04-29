<?php

namespace jurasciix\objeckson;

class Utils {

    /**
     * Конвертирует регистр текста из camelCase/PascalCase/UPPER_SNAKE_CASE в snake_case.
     */
    public static function toSnakeCase(string $text): string {
        return strtolower(preg_replace('/(?<=\p{Ll})\p{Lu}+\p{Ll}*/', '_$0', $text));
    }
}