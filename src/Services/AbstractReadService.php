<?php

namespace Giadc\DoctrineJsonApi\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\DoctrineJsonApi\Exceptions\EntityCannotBeFoundException;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiRepositoryInterface;
use Giadc\JsonApiRequest\Requests\RequestParams;

/**
 * @template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 */
abstract class AbstractReadService
{
    /**
     * @phpstan-var AbstractJsonApiRepositoryInterface<Entity>
     */
    protected AbstractJsonApiRepositoryInterface $repo;

    protected RequestParams $requestParams;

    protected string $entityReadableName;

    /**
     * Initialize the read service
     *
     * @phpstan-param AbstractJsonApiRepositoryInterface<Entity> $repo
     */
    public function initialize(
        AbstractJsonApiRepositoryInterface $repo,
        string $entityReadableName = 'Entity'
    ): void {
        $this->repo               = $repo;
        $this->entityReadableName = $entityReadableName;
        $this->requestParams      = new RequestParams();
    }

    /**
     * Find a single Entity by Id.
     *
     * @phpstan-param  string[] $additionalIncludes
     * @phpstan-return ?Entity
     */
    public function findById(string $id, array $additionalIncludes = []): ?object
    {
        $includes = $this->requestParams->getIncludes();
        $includes->add($additionalIncludes);

        $entity = $this->repo->findById($id, $includes);

        if (!$entity) {
            return null;
        }

        return $entity;
    }

    /**
     * Find a single Entity by Id or throw exception.
     *
     * @phpstan-param  string[] $additionalIncludes
     * @phpstan-return Entity
     */
    public function findByIdOrFail(string $id, array $additionalIncludes = []): object
    {
        $entity = $this->findById($id, $additionalIncludes);

        if ($entity === null) {
            throw new EntityCannotBeFoundException($this->entityReadableName, $id);
        }

        return $entity;
    }

    /**
     * Find Entities by array
     *
     * @phpstan-param array<mixed> $array
     * @phpstan-param  string[] $additionalIncludes
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByArray(
        array $array,
        string $field = 'id',
        array $additionalIncludes = []
    ): ArrayCollection {
        if (empty($array)) {
            return new ArrayCollection();
        }

        $includes = $this->requestParams->getIncludes();
        $includes->add($additionalIncludes);

        return $this->repo->findByArray($array, $field, $includes);
    }

    /**
     * Find an Entity by field value
     *
     * @phpstan-param  string[] $additionalIncludes
     * @phpstan-return ArrayCollection<string | int, Entity>
     */
    public function findByField(
        mixed $value,
        string $field = 'id',
        array $additionalIncludes = []
    ): ArrayCollection {
        $includes = $this->requestParams->getIncludes();
        $includes->add($additionalIncludes);

        return $this->repo->findByField($value, $field, $includes);
    }

    /**
     * Returns paginated list of Entities with
     * optional Filtering, Sorting, & Includes
     *
     * @phpstan-param  string[] $additionalIncludes
     * @phpstan-return Paginator<Entity>
     */
    public function paginate(array $additionalIncludes = []): Paginator
    {
        return $this->repo->paginateAll($this->requestParams, $additionalIncludes);
    }
}
