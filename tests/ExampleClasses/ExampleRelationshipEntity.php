<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Giadc\JsonApiResponse\Interfaces\JsonApiResource;

class ExampleRelationshipEntity implements JsonApiResource
{
    /** @var string */
    private $id;

    /** @var ExampleEntity|null */
    private $parent;

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
    public function id()
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


    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
        ];
    }
}
