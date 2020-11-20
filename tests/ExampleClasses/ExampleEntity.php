<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Giadc\JsonApiResponse\Interfaces\JsonApiResource;

class ExampleEntity implements JsonApiResource
{
    /** @var string **/
    private $id;

    /** @var string **/
    private $name;

    /** @var int **/
    private $width;


    /** @var int **/
    private $height;

    /** @var \DateTime **/
    private $runDate;

    /** @phpstan-var Collection<int, ExampleRelationshipEntity> **/
    private $relationships;

    public function __construct(
        string $id,
        string $name = 'Example Entity',
        int $width = 10,
        int $height = 20,
        \DateTime $runDate = null
    ) {
        $this->id      = $id;
        $this->name    = $name;
        $this->width   = $width;
        $this->height  = $height;
        $this->runDate = is_null($runDate) ? new \DateTime() : $runDate;

        $this->relationships = new ArrayCollection();
    }

    public static function getResourceKey(): string
    {
        return 'exampleEntity';
    }

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the value of width.
     *
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets the value of width.
     *
     * @param mixed $width the width
     * @return self
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Gets the value of height.
     *
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Sets the value of height.
     *
     * @param mixed $height the height
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Gets the value of runDate.
     *
     * @return mixed
     */
    public function getRunDate()
    {
        return $this->runDate;
    }

    /**
     * Sets the value of runDate.
     *
     * @param mixed $runDate the run date
     * @return self
     */
    public function setRunDate($runDate)
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
     *
     * @param  ExampleRelationshipEntity $relationship
     * @return self
     */
    public function addRelationship(ExampleRelationshipEntity $relationship)
    {
        $this->relationships->add($relationship);
        $relationship->setParent($this);

        return $this;
    }

    /**
     * Remove a relationship
     *
     * @param  ExampleRelationshipEntity $relationship
     * @return self
     */
    public function removeRelationship(ExampleRelationshipEntity $relationship)
    {
        if ($this->relationships->contains($relationship)) {
            $this->relationships->removeElement($relationship);
            $relationship->setParent(null);
        }

        return $this;
    }

    public function jsonSerialize()
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
