<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Doctrine\Common\Collections\ArrayCollection;

class ExampleEntity
{
    private $id;

    private $name;

    private $width;

    private $height;

    private $runDate;

    private $relationships;

    public function __construct($id, $name = 'Example Entity', $width = 10, $height = 20, \DateTime $runDate = null)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->width   = $width;
        $this->height  = $height;
        $this->runDate = is_null($runDate) ? new \DateTime() : $runDate;

        $this->relationships = new ArrayCollection();
    }

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
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
     * @return ArrayCollection
     */
    public function getRelationships()
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
            $this->relationships->remove($relationship);
            $relationship->setParent(null);
        }

        return $this;
    }
}
