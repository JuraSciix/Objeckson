<?php

namespace JuraSciix\Objeckson;

use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
class ClassAdapter {

    /**
     * @param ReflectionClass $reflection
     * @param array<string, Property> $properties
     */
    public function __construct(
        private readonly ReflectionClass $reflection,
        private readonly array $properties
    ) {}

    public function __invoke(mixed $data, Context $context): object {
        if (!is_array($data)) {
            $type = gettype($data);
            throw new TreeException("Array expected, $type got");
        }

        try {
            $instance = $this->reflection->newInstanceWithoutConstructor();
        } catch (ReflectionException $e) {
            throw new TreeException(
                message: "Unable to instantiate {$this->reflection->name}",
                previous: $e
            );
        }

        foreach ($this->properties as $property) {
            foreach ($property->keys as $key) {
                if (array_key_exists($key, $data)) {
                    try {
                        $value = $context->fromJson($data[$key], $property->type);
                    } catch (TreeException $e) {
                        throw new TreeException(
                            message: "Unable to map property \"$key\"",
                            previous: $e
                        );
                    }
                    ($property->accessor)($instance, $value);
                    continue 2;
                }
            }
            // Соответствие не найдено
            if ($property->required) {
                $keys = implode(", ", $property->keys);
                throw new TreeException("Unmatched required property with keys: $keys");
            }
        }

        return $instance;
    }

    public function __toString(): string {
        $buf = "{$this->reflection->name} {\n";
        foreach ($this->properties as $property) {
            $keys = implode(", ", $property->keys);
            $buf .= "\t$keys: $property->type\n";
        }
        $buf .= "}";
        return $buf;
    }
}