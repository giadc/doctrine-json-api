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
     *
     * @return Paginator
     * @phpstan-return Paginator<Entity>
     */
    public function paginateAll(
        RequestParams $params,
        array $additionalIncludes = []
    );

    /**
     * Find an entity by ID.
     *
     * @param string | int $value
     *
     * @return ?object
     * @phpstan-return Entity | null
     */
    public function findById($value, Includes $includes = null);

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
    );

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
    );

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
    );

    /**
     * Updates or creates an Entity.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function createOrUpdate($entity);

    /**
     * Update an existing Entity.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function update($entity, bool $mute = false);

    /**
     * Add a new Entity to the database.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function add($entity, bool $mute = false);

    /**
     * Delete an Entity from the database.
     *
     * @param object $entity
     * @phpstan-param Entity $entity
     *
     * @return void
     */
    public function delete($entity, bool $mute = false);

    /**
     * Flush pending changes to the database.
     *
     * @return void
     */
    public function flush();

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     *
     * @return void
     */
    public function clear();
}
