<?php
namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\JsonApiRequest\Requests\Filters;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\RequestParams;
use Giadc\JsonApiRequest\Requests\Sorting;

interface AbstractJsonApiRepositoryInterface
{
    /**
     * Paginate entities with Includes, Sorting, and Filters
     *
     * @param  RequestParams $params
     * @param  array $additionalIncludes
     * @return Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginateAll(RequestParams $params, array $additionalIncludes = []);

    /**
     * Find an entity by ID
     *
     * @param  string        $value
     * @param  Includes|null $includes
     * @return mixed
     */
    public function findById(string $value, Includes $includes = null);

    /**
     * Find entity by field value
     *
     * @param  mixed         $value
     * @param  string        $field
     * @param  Includes|null $includes
     * @return mixed
     */
    public function findOneByField($value, string $field = 'id', Includes $includes = null);

    /**
     * Find entities by field value
     *
     * @param  mixed         $value
     * @param  string        $field
     * @param  Includes|null $includes
     * @return ArrayCollection
     */
    public function findByField($value, string $field = 'id', Includes $includes = null);

    /**
     * Find enties by an array of field values
     *
     * @param  array         $array
     * @param  string        $field
     * @param  Includes|null $includes
     * @return ArrayCollection
     */
    public function findByArray(array $array, string $field = 'id', Includes $includes = null);

    /**
     * Updates or creates an Entity
     *
     * @param $entity
     * @return mixed
     */
    public function createOrUpdate($entity);

    /**
     * Update an existing Entity
     *
     * @param $entity
     * @return void
     */
    public function update($entity, bool $mute = false);

    /**
     * Add a new Entity to the database
     *
     * @param mixed   $entity
     * @param boolean $mute
     */
    public function add($entity, bool $mute = false);

    /**
     * Delete an Entity from the database
     *
     * @param  mixed   $entity
     * @param  boolean $mute
     */
    public function delete($entity, bool $mute = false);

    /**
     * Flush pending changes to the database
     */
    public function flush();

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     */
    public function clear();
}
