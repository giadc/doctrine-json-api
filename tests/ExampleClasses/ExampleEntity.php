<?php

namespace Giadc\DoctrineJsonApi\Tests;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Giadc\JsonApiResponse\Interfaces\JsonApiResource;

class ExampleEntity implements JsonApiResource
{
    private string|int $id;

    private string $name;

    private int $width;

    private int $height;

    private DateTime $runDate;

    /** @phpstan-var Collection<int, ExampleRelationshipEntity> **/
    private Collection $relationships;

    public function __construct(
        string $id,
        string $name = 'Example Entity',
        int $width = 10,
        int $height = 20,
        DateTime $runDate = null
    ) {
        $this->id      = $id;
        $this->name    = $name;
        $this->width   = $width;
        $this->height  = $height;
        $this->runDate = is_null($runDate) ? new DateTime() : $runDate;

        $this->relationships = new ArrayCollection();
    }

    public static function getResourceKey(): string
    {
        return 'exampleEntity';
    }

    /**
     * Gets the value of id.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Gets the value of name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the value of width.
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Sets the value of width.
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Gets the value of height.
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Sets the value of height.
     */
    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Gets the value of runDate.
     */
    public function getRunDate(): DateTime
    {
        return $this->runDate;
    }

    /**
     * Sets the value of runDate.
     */
    public function setRunDate(DateTime $runDate): self
    {
        $this->runDate = $runDate;
        return $this;
    }

    /**
     * Get the relationships
     *
     * @phpstan-return Collection<int, ExampleRelationshipEntity>
     */
    public function getRelationships(): Collection
    {
        return $this->relationships;
    }

    /**
     * Add a new relationship
     */
    public function addRelationship(ExampleRelationshipEntity $relationship): self
    {
        $this->relationships->add($relationship);
        $relationship->setParent($this);

        return $this;
    }

    /**
     * Remove a relationship
     */
    public function removeRelationship(ExampleRelationshipEntity $relationship): self
    {
        if ($this->relationships->contains($relationship)) {
            $this->relationships->removeElement($relationship);
            $relationship->setParent(null);
        }

        return $this;
    }

    /**
     * @phpstan-return array<string, string|int|bool>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'width' => $this->width,
            'height' => $this->height,
            'runDate' => $this->runDate->format(DATE_ISO8601),
        ];
    }
}
