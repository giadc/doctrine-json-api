<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Giadc\JsonApiResponse\Interfaces\JsonApiResource;

class ExampleRelationshipEntity implements JsonApiResource
{
    private string $id;

    private ?ExampleEntity $parent = null;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function getResourceKey(): string
    {
        return 'relationship';
    }

    /**
     * Gets the value of id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Set the Parent
     *
     * @param ExampleEntity|null $parent
     */
    public function setParent(ExampleEntity $parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?ExampleEntity
    {
        return $this->parent;
    }


    /**
     * @phpstan-return array<string, string|int|bool>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
