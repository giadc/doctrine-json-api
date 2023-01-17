<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Doctrine\ORM\EntityManager;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiRepositoryInterface as JsonApiInterface;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiDoctrineRepository as JsonApiRepository;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;

/**
 * @phpstan-extends JsonApiRepository<ExampleEntity>
 * @phpstan-implements JsonApiInterface<ExampleEntity>
 */
class ExampleRepository extends JsonApiRepository implements
    JsonApiInterface
{
    protected EntityManager $em;

    protected string $class;

    protected ExampleFilters $filters;

    /**
     * Create a new ExampleDoctrineRepository
     */
    public function __construct(EntityManager $em, ExampleFilters $filters = null)
    {
        $this->em = $em;
        $this->class = ExampleEntity::class;
        $this->filters = $filters;
    }
}
