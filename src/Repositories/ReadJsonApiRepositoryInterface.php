<?php

namespace Giadc\DoctrineJsonApi\Repositories;

use Doctrine\Common\Collections\ArrayCollection;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\RequestParams;
use Giadc\JsonApiResponse\Pagination\FractalDoctrinePaginatorAdapter;

/**
 * @template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 */
interface ReadJsonApiRepositoryInterface
{
    /**
     * Paginate entities with Includes, Sorting, and Filters.
     *
     * @phpstan-param array<string> $additionalIncludes
     */
    public function paginateAll(
        RequestParams $params,
        array $additionalIncludes = []
    ): FractalDoctrinePaginatorAdapter;

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
}
