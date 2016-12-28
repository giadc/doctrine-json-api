<?php
namespace Giadc\DoctrineJsonApi\Services;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @param  AbstractJsonApiRepositoryInterface $repo
     * @return void
     */
    public function initialize($repo, $entityReadableName = 'Entity')
    {
        $this->repo               = $repo;
        $this->entityReadableName = $entityReadableName;
        $this->requestParams      = new RequestParams();
    }

    /**
     * Find a single Entity
     *
     * @param  string $id
     * @param  array $additionalIncludes
     * @return mixed
     */
    public function findById($id, $additionalIncludes = [])
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
     * Find Entities by array
     *
     * @param  array  $array
     * @param  string $field
     * @param  array  $additionalIncludes
     * @return array
     */
    public function findByArray($array, $field = 'id', $additionalIncludes = [])
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
     * @param  string $field
     * @param  string $value
     * @param  array  $additionalIncludes
     * @return mixed
     */
    public function findByField($value, $field = 'id', $additionalIncludes = [])
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
    public function paginate($additionalIncludes = [])
    {
        return $this->repo->paginateAll($this->requestParams, $additionalIncludes);
    }
}
