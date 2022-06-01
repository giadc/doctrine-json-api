<?php

namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\RequestParams;

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
