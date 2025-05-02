<?php

namespace jurasciix\objeckson;

use InvalidArgumentException;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

class AdapterContext {

    private readonly AdaptTreeFactory $adaptTreeFactory;

    /**
     * @var array<string, callable>
     */
    private array $adapters = [];

    public function __construct() {
        $this->adaptTreeFactory = new AdaptTreeFactory();

        $this->registerBuiltin();
    }

    private function registerBuiltin(): void {
        // todo: built-in adapter factories for: DateTime, SplFixedArray
        $this->withAdapter(new IdentifierTypeNode('int'), fn ($x) => intval($x))
            ->withAdapter(new IdentifierTypeNode('integer'), fn ($x) => intval($x))
            ->withAdapter(new IdentifierTypeNode('float'), fn($x) => floatval($x))
            ->withAdapter(new IdentifierTypeNode('bool'), fn($x) => boolval($x))
            ->withAdapter(new IdentifierTypeNode('boolean'), fn($x) => boolval($x))
            ->withAdapter(new IdentifierTypeNode('string'), fn($x) => strval($x))
            ->withAdapter(new IdentifierTypeNode('mixed'), fn($x) => $x);
    }

    public function fromJson(mixed $data, TypeNode $node): mixed {
        if ($this->hasAdapter($node)) {
            $adapter = $this->getAdapter($node);
        } else {
            // Не храним nullable-адаптеры в памяти.
            if ($node instanceof NullableTypeNode) {
                if ($data === null)
                    return null;
                return $this->fromJson($data, $node->type);
            }
            if ($node instanceof ArrayTypeNode) {
                $adapter = new ArrayAdapter(new IdentifierTypeNode('mixed'), $node->type);
            } else if ($node instanceof ArrayShapeNode) {
                $adapter = new ArrayShapeAdapter($node->items);
            } else if ($node instanceof IdentifierTypeNode) {
                $adapter = ($this->adaptTreeFactory)($node, []);
            } else if ($node instanceof GenericTypeNode) {
                if (Utils::isTypeNodeArray($node->type)) {
                    $genericsNum = sizeof($node->genericTypes);
                    if ($genericsNum === 1) {
                        $adapter = new ArrayAdapter(new IdentifierTypeNode('mixed'), $node->type);
                    } else if ($genericsNum === 2){
                        $adapter = new ArrayAdapter($node->genericTypes[0], $node->genericTypes[1]);
                    } else {
                        throw new InvalidArgumentException("Array can have at most 2 arguments, but $genericsNum");
                    }
                } else {
                    $adapter = ($this->adaptTreeFactory)($node->type, $node->genericTypes);
                }
            } else {
                throw new InvalidArgumentException("Illegal type node: " . get_class($node));
            }
            $this->withAdapter($node, $adapter);
        }

        return $adapter($data, $this);
    }

    /**
     * @internal
     */
    public function withAdapter(TypeNode $type, callable $adapter): self {
        $this->adapters[strval($type)] = $adapter;
        return $this;
    }

    /**
     * @internal
     */
    public function hasAdapter(TypeNode $type): bool {
        return array_key_exists(strval($type), $this->adapters);
    }

    /**
     * @internal
     */
    public function getAdapter(TypeNode $type): ?callable {
        return $this->adapters[strval($type)] ?? null;
    }
}