<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Doctrine\ORM\Mapping as ORM;
use Giadc\JsonApiResponse\Interfaces\JsonApiResource;

#[ORM\Entity]
#[ORM\Table(name: 'example_relationship_entities')]
class ExampleRelationshipEntity implements JsonApiResource
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 2)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: ExampleEntity::class, inversedBy: 'relationships')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
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
