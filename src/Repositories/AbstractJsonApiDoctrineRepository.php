<?php

declare(strict_types=1);

namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\Mapping\MappingException;
use Giadc\DoctrineJsonApi\Pagination\FractalDoctrinePaginatorAdapter;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\RequestParams;
use Giadc\JsonApiRequest\Requests\Sorting;
use Giadc\JsonApiResponse\Pagination\PaginatedCollection;

/**
 * @phpstan-template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 * @phpstan-type SortDetails array{field: string, direction: 'ASC'|'DESC'}
 * @phpstan-type SortArray array<string, SortDetails>
 */
abstract class AbstractJsonApiDoctrineRepository
{
    /**
     * @phpstan-var class-string<Entity>
     */
    protected string $class;

    protected EntityManager $em;

    /**
     * Get the default Sorting for the repository.
     *
     * example: ['domain' => ['field' => 'domain', 'direction' => 'ASC']]
     * @phpstan-return SortArray
     */
    protected function getDefaultSort(): array
    {
        return [];
    }

    /**
     * Paginate entities with Includes, Sorting, and Filters.
     *
     * @phpstan-param array<string> $additionalIncludes
     * @phpstan-return PaginatedCollection<int|string, Entity>
     */
    public function paginateAll(
        RequestParams $params,
        array $additionalIncludes = []
    ): PaginatedCollection {
        $qb = $this->em->createQueryBuilder();
        $includes = $params->getIncludes();
        $includes->add($additionalIncludes);

        $qb->select('e')->from($this->class, 'e');

        $qb = $this->processSorting($qb, $params->getSortDetails());
        $qb = $this->processIncludes($qb, $includes);

        if ($params->getFiltersDetails() != null && isset($this->filters)) {
            $qb = $this->filters->process($qb, $params->getFiltersDetails());
        }

        $paginator = $this->paginate($qb, $params->getPageDetails());
        $fractalPaginator = new FractalDoctrinePaginatorAdapter($paginator, $params);
        return new PaginatedCollection((array) $paginator->getIterator(), $fractalPaginator);
    }

    /**
     * Find an entity by ID.
     *
     * @phpstan-return Entity|null
     */
    public function findById(string|int $value, Includes $includes = null): ?object
    {
        $results = $this->findByField($value, 'id', $includes);

        return $results == null ? null : $results[0];
    }

    /**
     * Find entity by field value.
     *
     * @phpstan-return Entity|null
     */
    public function findOneByField(
        mixed $value,
        string $field = 'id',
        Includes $includes = null
    ): ?object {
        $results = $this->findByField($value, $field, $includes);

        return $results == null ? null : $results[0];
    }

    /**
     * Find entities by field value.
     *
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByField(
        mixed $value,
        string $field = 'id',
        Includes $includes = null
    ): ArrayCollection {
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
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByArray(
        array $array,
        string $field = 'id',
        Includes $includes = null
    ): ArrayCollection {
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
     * @phpstan-param Entity $entity
     */
    public function createOrUpdate(object $entity): void
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
     * @phpstan-param Entity $entity
     */
    public function update(object $entity, bool $mute = false): void
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
     * @phpstan-param Entity $entity
     */
    public function add(object $entity, bool $mute = false): void
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
     * @phpstan-param Entity $entity
     */
    public function delete(object $entity, bool $mute = false): void
    {
        $this->isValidEntity($entity);
        $this->em->remove($entity);

        if (!$mute) {
            $this->em->flush();
        }
    }

    /**
     * Flush pending changes to the database.
     */
    public function flush(): void
    {
        $this->em->flush();
    }

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     */
    public function clear(): void
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

        return $metadata->getAssociationMappings()[$association]['targetEntity'] ?? null;
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
     * @phpstan-return ClassMetadata<Entity>|null
     */
    protected function getClassMetadata(string $class = null): ?ClassMetadata
    {
        try {
            /** @phpstan-ignore-next-line */
            return $this->em->getClassMetadata($class ?? $this->class);
        } catch (MappingException $e) {
            return null;
        }
    }
}
