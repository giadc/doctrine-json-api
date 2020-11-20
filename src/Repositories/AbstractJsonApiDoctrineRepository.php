<?php

namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\Mapping\MappingException;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\RequestParams;
use Giadc\JsonApiRequest\Requests\Sorting;

/**
 * @template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 */
abstract class AbstractJsonApiDoctrineRepository
{
    /**
     * @var string
     * @phpstan-var class-string<Entity>
     */
    protected $class;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Get the default Sorting for the repository.
     *
     * example: ['domain' => ['field' => 'domain', 'direction' => 'ASC']]
     *
     * @phpstan-return array <string, array{field: string, direction: string}>
     */
    protected function getDefaultSort(): array
    {
        return [];
    }

    /**
     * Paginate entities with Includes, Sorting, and Filters.
     *
     * @phpstan-param array<string> $additionalIncludes
     *
     * @return Paginator
     * @phpstan-return Paginator<Entity>
     */
    public function paginateAll(
        RequestParams $params,
        array $additionalIncludes = []
    ) {
        $qb = $this->em->createQueryBuilder();
        $includes = $params->getIncludes();
        $includes->add($additionalIncludes);

        $qb->select('e')->from($this->class, 'e');

        $qb = $this->processSorting($qb, $params->getSortDetails());
        $qb = $this->processIncludes($qb, $includes);

        if ($params->getFiltersDetails() != null && isset($this->filters)) {
            $qb = $this->filters->process($qb, $params->getFiltersDetails());
        }

        return $this->paginate($qb, $params->getPageDetails());
    }

    /**
     * Find an entity by ID.
     *
     * @param string | int $value
     *
     * @return ?object
     * @phpstan-return Entity | null
     */
    public function findById($value, Includes $includes = null)
    {
        $results = $this->findByField($value, 'id', $includes);

        return $results == null ? null : $results[0];
    }

    /**
     * Find entity by field value.
     *
     * @param  mixed $value
     *
     * @return ?object
     * @phpstan-return Entity | null
     */
    public function findOneByField(
        $value,
        string $field = 'id',
        Includes $includes = null
    ) {
        $results = $this->findByField($value, $field, $includes);

        return $results == null ? null : $results[0];
    }

    /**
     * Find entities by field value.
     *
     * @param mixed $value
     *
     * @return ArrayCollection
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByField(
        $value,
        string $field = 'id',
        Includes $includes = null
    ) {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('e')
            ->from($this->class, 'e')
            ->where('e.' . $field . ' = ?1');

        if ($includes) {
            $qb = $this->processIncludes($qb, $includes);
        }

        $qb->setParameter(1, $value);

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * Find entities by an array of field values.
     *
     * @phpstan-param array<mixed> $array
     *
     * @return ArrayCollection
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByArray(
        array $array,
        string $field = 'id',
        Includes $includes = null
    ) {
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
     * Updates or creates an Entity.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function createOrUpdate($entity)
    {
        $this->isValidEntity($entity);

        $e = $this->findById($entity->id());

        if ($e == null) {
            $this->add($entity);
        } else {
            $this->update($entity);
        }
    }

    /**
     * Update an existing Entity.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function update($entity, bool $mute = false)
    {
        $this->isValidEntity($entity);
        $this->em->merge($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Add a new Entity to the database.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function add($entity, bool $mute = false)
    {
        $this->isValidEntity($entity);
        $this->em->persist($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Delete an Entity from the database.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function delete($entity, bool $mute = false)
    {
        $this->isValidEntity($entity);
        $this->em->remove($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Flush pending changes to the database.
     *
     * @return void
     */
    public function flush()
    {
        $this->em->flush();
    }

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     *
     * @return void
     */
    public function clear()
    {
        $this->em->clear();
    }

    /**
     * Is the given Entity a valid member of this Repository.
     *
     * @phpstan-param  Entity $entity
     */
    protected function isValidEntity(object $entity): void
    {
        if (!is_a($entity, $this->class)) {
            throw new \Exception('Invalid Entity: ' . get_class($entity));
        }
    }

    // Processors

    /**
     * Add SELECT and LEFT JOIN statements for all includes.
     */
    protected function processIncludes(
        QueryBuilder $qb,
        Includes $includes
    ): QueryBuilder {
        foreach ($includes->toArray() as $include) {
            strpos($include, '.') === false
                ? $this->processSingleInclude($qb, $include)
                : $this->processNestedInclude($qb, $include);
        }

        return $qb;
    }

    /**
     * Process a single include.
     */
    protected function processSingleInclude(
        QueryBuilder $qb,
        string $include
    ): void {
        if (
            !$this->hasAssociation($include) ||
            $this->includeExists($qb, $include)
        ) {
            return;
        }

        $qb->leftJoin('e.' . $include, $include);
        $qb->addSelect($include);
    }

    /**
     * Process nested includes.
     */
    protected function processNestedInclude(
        QueryBuilder $qb,
        string $include
    ): void {
        $includePieces = explode('.', $include);
        $parentClass = $this->class;

        // prepend doctrine entity key.
        array_unshift($includePieces, 'e');

        for ($i = 1; $i < count($includePieces); ++$i) {
            if (!$this->hasAssociation($includePieces[$i], $parentClass)) {
                return;
            }

            $parentClass = $this->getAssociationClass(
                $includePieces[$i],
                $parentClass
            );

            if ($this->includeExists($qb, $includePieces[$i])) {
                continue;
            }

            $qb->leftJoin(
                $includePieces[$i - 1] . '.' . $includePieces[$i],
                $includePieces[$i]
            );
            $qb->addSelect($includePieces[$i]);
        }
    }

    /**
     * Determines if an include has already been processed.
     */
    protected function includeExists(QueryBuilder $qb, string $include): bool
    {
        foreach ($qb->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $include) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process Sorting.
     */
    protected function processSorting(
        QueryBuilder $qb,
        Sorting $sort
    ): QueryBuilder {
        $sortArray = $sort->toArray();

        if (empty(array_filter($sortArray))) {
            $sortArray = $this->getDefaultSort();
        }

        foreach ($sortArray as $field) {
            if ($this->hasField($field['field'])) {
                $qb->addOrderBy('e.' . $field['field'], $field['direction']);
            } elseif ($this->hasAssociation($field['field'])) {
                $qb->addOrderBy($field['field'], $field['direction']);
            }
        }

        return $qb;
    }

    /**
     * Add offset and page size.
     *
     * @phpstan-return Paginator<Entity>
     */
    public function paginate(QueryBuilder $query, Pagination $page): Paginator
    {
        $query = $query
            ->setParameters($query->getParameters())
            ->setFirstResult($page->getOffset() * $page->getPageSize())
            ->setMaxResults($page->getPageSize());

        return new Paginator($query, true);
    }

    /**
     * Determine if the current Class has a field.
     */
    protected function hasField(string $field): bool
    {
        $fields = $this->getClassMetadata($this->class);

        return $fields && $fields->hasField($field);
    }

    /**
     * Retrieves Class Namespace for an association.
     */
    protected function getAssociationClass(
        string $association,
        string $class = null
    ): ?string {
        $metadata = $this->getClassMetadata($class);

        if (
            $metadata === null ||
            !array_key_exists($association, $metadata->getAssociationMappings())
        ) {
            return null;
        }

        return $metadata->getAssociationMappings()[$association][
            'targetEntity'
        ] ?? null;
    }

    /**
     * Determine if the current Class has an association.
     */
    protected function hasAssociation(
        string $association,
        string $class = null
    ): bool {
        $metadata = $this->getClassMetadata($class);

        if ($metadata === null) {
            return false;
        }

        return array_key_exists(
            $association,
            $metadata->getAssociationMappings()
        );
    }

    /**
     * Return the metadata mapping for the current Class.
     */
    protected function getClassMetadata(string $class = null): ?ClassMetadata
    {
        try {
            return $this->em->getClassMetadata($class ?? $this->class);
        } catch (MappingException $e) {
            return null;
        }
    }
}
