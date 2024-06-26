<?php

namespace jurasciix\objeckson;

use Iterator;

/**
 * @internal
 */
final class Utils {

    /**
     * Конвертирует регистр текста из camelCase/PascalCase/UPPER_SNAKE_CASE в snake_case.
     */
    public static function toSnakeCase(string $text): string {
        return strtolower(preg_replace('/(?<=\p{Ll})\p{Lu}+\p{Ll}*/', '_$0', $text));
    }

    /**
     * Возвращает итератор вариантов для названий сеттера для поля $fieldName.
     */
    public static function getSetterNameVariants(string $fieldName): Iterator {
        // todo: Детальная конвертация регистров. Пример: __block => Block
        $trimmedFieldName = trim($fieldName, '_');
        yield "set" . ucfirst($trimmedFieldName);
        yield $trimmedFieldName;
        yield $fieldName;
    }
}