<?php

namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\RequestParams;

/**
 * @template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 */
interface AbstractJsonApiRepositoryInterface
{
    /**
     * Paginate entities with Includes, Sorting, and Filters.
     *
     * @phpstan-param array<string> $additionalIncludes
     * @phpstan-return Paginator<Entity>
     */
    public function paginateAll(
        RequestParams $params,
        array $additionalIncludes = []
    ): Paginator;

    /**
     * Find an entity by ID.
     *
     * @phpstan-return Entity | null
     */
    public function findById(
        string|int $value,
        Includes $includes = null
    ): ?object;

    /**
     * Find entity by field value.
     *
     * @phpstan-return Entity | null
     */
    public function findOneByField(
        mixed $value,
        string $field = 'id',
        Includes $includes = null
    ): ?object;

    /**
     * Find entities by field value.
     *
     * @return ArrayCollection
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByField(
        mixed $value,
        string $field = 'id',
        Includes $includes = null
    ): ArrayCollection;

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
    ): ArrayCollection;

    /**
     * Updates or creates an Entity.
     *
     * @phpstan-param Entity $entity
     */
    public function createOrUpdate(object $entity): void;

    /**
     * Update an existing Entity.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     */
    public function update(object $entity, bool $mute = false): void;

    /**
     * Add a new Entity to the database.
     *
     * @phpstan-param Entity $entity
     */
    public function add(object $entity, bool $mute = false): void;

    /**
     * Delete an Entity from the database.
     *
     * @phpstan-param Entity $entity
     */
    public function delete(object $entity, bool $mute = false): void;

    /**
     * Flush pending changes to the database.
     */
    public function flush(): void;

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     */
    public function clear(): void;
}
