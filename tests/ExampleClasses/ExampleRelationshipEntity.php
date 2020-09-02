<?php

namespace Giadc\DoctrineJsonApi\Tests;

class ExampleRelationshipEntity
{
    /** @var string */
    private $id;

    /** @var ExampleEntity */
    private $parent;

    /** @var string */
    private $firstName= 'fake';

    /** @var string */
    private $lastName = 'name';

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Gets the value of id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the Parent
     *
     * @param ExampleEntity|null $parent
     */
    public function setParent(ExampleEntity $parent = null)
    {
        $this->parent = $parent;
    }
}
