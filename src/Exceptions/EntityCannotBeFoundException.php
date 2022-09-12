<?php

namespace Giadc\DoctrineJsonApi\Exceptions;

class EntityCannotBeFoundException extends \Exception
{
    private string $id;

    private string $entityName;

    public function __construct(string $entityName, string $id, \Exception $previous = null, int $code = 0)
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

    public function id(): string
    {
        return $this->id;
    }

    public function entityName(): string
    {
        return $this->entityName;
    }
}
