<?php
namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Giadc\DoctrineJsonApi\Repositories\Processors;
use Giadc\JsonApiRequest\Requests\Filters;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\Sorting;

abstract class AbstractJsonApiDoctrineRepository
{
    use Processors;

    protected $class;

    /**
     * Get the default Sorting for the repository
     *
     * @return array
     */
    protected function getDefaultSort()
    {
        return [];
    }

    /**
     * Paginate entities with Includes, Sorting, and Filters
     *
     * @param  Pagination $page
     * @param  Includes   $includes
     * @param  Sorting    $sort
     * @param  Filters    $filters
     * @return Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginateAll(Pagination $page, Includes $includes, Sorting $sort, Filters $filters)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('e')
            ->from($this->class, 'e');

        $qb = $this->processSorting($qb, $sort);
        $qb = $this->processIncludes($qb, $includes);

        if (isset($this->filters)) {
            $qb = $this->filters->process($qb, $filters);
        }

        return $this->paginate($qb, $page);
    }

    /**
     * Find an entity by ID
     *
     * @param  string        $value
     * @param  Includes|null $includes
     * @return mixed
     */
    public function findById($value, Includes $includes = null)
    {
        $results = $this->findByField($value, 'id', $includes);
        return $results == null ? null : $results[0];
    }

    /**
     * Find entities by field value
     *
     * @param  mixed         $value
     * @param  string        $field
     * @param  Includes|null $includes
     * @return ArrayCollection
     */
    public function findByField($value, $field = 'id', Includes $includes = null)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('e')
            ->from($this->class, 'e')
            ->where('e.' . $field . ' = ?1');

        if ($includes) {
            $qb = $this->processIncludes($qb, $includes);
        }

        $qb->setParameter(1, $value);

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * Find enties by an array of field values
     *
     * @param  array         $array
     * @param  string        $field
     * @param  Includes|null $includes
     * @return ArrayCollection
     */
    public function findByArray($array, $field = 'id', Includes $includes = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e');
        $qb->from($this->class, 'e');
        $qb->where($qb->expr()->in('e.' . $field, $array));

        if ($includes) {
            $qb = $this->processIncludes($qb, $includes);
        }

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * Updates or creates an Entity
     *
     * @param $entity
     * @return void
     */
    public function createOrUpdate($entity)
    {
        $this->isValidEntity($entity);

        $e = $this->findById($entity->getId());

        if ($e == null) {
            return $this->add($entity);
        }

        return $this->update($entity);
    }

    /**
     * Update an existing Entity
     *
     * @param $entity
     * @return void
     */
    public function update($entity, $mute = false)
    {
        $this->isValidEntity($entity);

        $this->em->merge($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Add a new Entity to the database
     *
     * @param mixed   $entity
     * @param boolean $mute
     */
    public function add($entity, $mute = false)
    {
        $this->isValidEntity($entity);

        $this->em->persist($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Delete an Entity from the database
     *
     * @param  mixed   $entity
     * @param  boolean $force
     * @param  boolean $mute
     */
    public function delete($entity, $mute = false)
    {
        $this->isValidEntity($entity);

        $this->em->remove($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Flush pending changes to the database
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     */
    public function clear()
    {
        $this->em->clear();
    }

    /**
     * Is the given Entity a valid member of this Repository
     *
     * @param  mixed $entity
     * @return boolean
     */
    protected function isValidEntity($entity)
    {
        if (!is_a($entity, $this->class)) {
            throw new \Exception('Invalid Entity: ' . get_class($entity));
        }
    }

}
