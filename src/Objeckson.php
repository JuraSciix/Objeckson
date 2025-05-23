<?php

namespace JuraSciix\Objeckson;

use Closure;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;

class Objeckson {

    /**
     * Создаёт статичный экземпляр класса Objeckson.
     *
     * @return Objeckson Статичный экземпляр.
     */
    public static function get() {
        return ObjecksonHolder::$instance ??= new Objeckson();
    }

    // todo: конфигурирование: enableCamelCase, disableSnakeCase, enableErrorOnUnmapped

    private readonly Closure $jsonDeserializer;

    private readonly Context $context;

    public function __construct(
        callable $jsonDeserializer = new JsonDecode(),
        Context $context = new Context()
    ) {
        $this->jsonDeserializer = $jsonDeserializer(...);
        $this->context = $context;
    }

    /**
     * @template TObject
     * @param class-string<TObject> $type
     * @return TObject
     */
    public function fromJson(array|string $data, string $type): object {
        if (is_string($data)) {
            $data = ($this->jsonDeserializer)($data);
        }
        $typeNode = new IdentifierTypeNode($type);
        return $this->context->fromJson($data, $typeNode);
    }

    /**
     * @template TComponent
     * @param class-string<TComponent> $type
     * @return TComponent[]
     */
    public function arrayFromJson(array|string $data, string $type): array {
        if (is_string($data)) {
            $data = ($this->jsonDeserializer)($data);
        }
        $typeNode = new ArrayTypeNode(new IdentifierTypeNode($type));
        return $this->context->fromJson($data, $typeNode);
    }

    /**
     * @template TComponent
     * @param class-string<TComponent> $type
     * @param string ...$generics
     * @return TComponent
     */
    public function fromJsonWithGenerics(array|string $data, string $type, string ...$generics): mixed {
        if (is_string($data)) {
            $data = ($this->jsonDeserializer)($data);
        }
        $genericNodes = [];
        foreach ($generics as $generic) {
            $genericNodes[] = new IdentifierTypeNode($generic);
        }
        $typeNode = new GenericTypeNode(new IdentifierTypeNode($type), $genericNodes);
        return $this->context->fromJson($data, $typeNode);
    }
}