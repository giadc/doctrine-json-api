<?php
namespace Giadc\DoctrineJsonApi\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Giadc\DoctrineJsonApi\Exceptions\EntityCannotBeFoundException;
use Giadc\JsonApiRequest\Requests\RequestParams;

abstract class AbstractReadService
{
    /** @var AbstractJsonApiRepositoryInterface */
    protected $repo;

    /** @var RequestParams */
    protected $requestParams;

    /** @var string */
    protected $entityReadableName;

    /**
     * Initialize the read service
     *
     * @param mixed $repo
     * @param string $entityReadableName
     */
    public function initialize($repo, string $entityReadableName = 'Entity')
    {
        $this->repo               = $repo;
        $this->entityReadableName = $entityReadableName;
        $this->requestParams      = new RequestParams();
    }

    /**
     * Find a single Entity by Id.
     *
     * @param  string $id
     * @param  array $additionalIncludes
     * @return mixed
     */
    public function findById(string $id, array $additionalIncludes = [])
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
     * @param  string $id
     * @param  array $additionalIncludes
     * @return mixed
     */
    public function findByIdOrFail(string $id, array $additionalIncludes = [])
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
     * @param  array  $array
     * @param  string $field
     * @param  array  $additionalIncludes
     * @return array
     */
    public function findByArray(array $array, string $field = 'id', array $additionalIncludes = [])
    {
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
     * @param  mixed $value
     * @param  string $field
     * @param  array  $additionalIncludes
     * @return mixed
     */
    public function findByField($value, string $field = 'id', array $additionalIncludes = [])
    {
        $includes = $this->requestParams->getIncludes();
        $includes->add($additionalIncludes);

        return $this->repo->findByField($value, $field, $includes);
    }

    /**
     * Returns paginated list of Entities with
     * optional Filtering, Sorting, & Includes
     *
     * @param  array $additionalIncludes
     * @return array
     */
    public function paginate(array $additionalIncludes = [])
    {
        return $this->repo->paginateAll($this->requestParams, $additionalIncludes);
    }
}
