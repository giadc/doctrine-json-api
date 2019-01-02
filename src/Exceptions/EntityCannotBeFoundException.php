<?php

namespace Giadc\DoctrineJsonApi\Exceptions;

class EntityCannotBeFoundException extends \Exception
{
    private $id;

    private $entityName;

    public function __construct($entityName, $id, \Exception $previous = null, $code = 0)
    {
        $this->id = $id;
        $this->entityName = $entityName;

        $message = sprintf(
            'Entity not found: %s ( %s ).',
            $entityName,
            $id
        );

        parent::__construct($message, $code, $previous);
    }

    public function id()
    {
        return $this->id;
    }

    public function entityName()
    {
        return $this->entityName;
    }
}

