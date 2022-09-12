<?php

declare(strict_types=1);

namespace Giadc\DoctrineJsonApi\Repositories;

/**
 * @template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 */
interface WriteJsonApiRepositoryInterface
{
    /**
     * Updates or creates an Entity.
     *
     * @phpstan-param Entity $entity
     */
    public function createOrUpdate(object $entity): void;

    /**
     * Update an existing Entity.
     *
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
