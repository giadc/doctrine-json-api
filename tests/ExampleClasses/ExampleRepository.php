<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiRepositoryInterface as JsonApiInterface;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiDoctrineRepository as JsonApiRepository;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;

/**
 * @phpstan-extends JsonApiRepository<ExampleEntity>
 */
class ExampleRepository extends JsonApiRepository implements
    JsonApiInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ExampleFilters|null
     */
    protected $filters;

    /**
     * Create a new ExampleDoctrineRepository
     *
     * @param EntityManager $em
     * @return void
     */
    public function __construct(EntityManager $em, ExampleFilters $filters = null)
    {
        $this->em      = $em;
        $this->class   = ExampleEntity::class;
        $this->filters = $filters;
    }
}
