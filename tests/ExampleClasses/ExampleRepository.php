<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Giadc\DoctrineJsonApi\Interfaces\AbstractJsonApiRepositoryInterface as JsonApiInterface;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiDoctrineRepository as JsonApiRepository;
use Giadc\JsonApiRequest\Requests\Filters;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Sorting;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;

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
     * @var ExampleFilters
     */

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

    /**
     * Get all Entities
     *
     * @param  Includes $includes
     * @param  Sorting  $sort
     * @param  Filters  $filters
     * @return array
     */
    public function all(Includes $includes, Sorting $sort, Filters $filters)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('e')
            ->from($this->class, 'e');

        $qb = $this->processSorting($qb, $sort);
        $qb = $this->processIncludes($qb, $includes);

        if (isset($this->filters)) {
            $qb = $this->filters->process($qb, $filters);
        }

        return new ArrayCollection($qb->getQuery()->getResult());
    }
}
