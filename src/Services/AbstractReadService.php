<?php

namespace Giadc\DoctrineJsonApi\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Giadc\JsonApiRequest\Requests\RequestParams;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;

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

        if ($additionalIncludes) {
            $includes->add($additionalIncludes);
        }

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

        if ($additionalIncludes) {
            $includes->add($additionalIncludes);
        }

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

        if ($additionalIncludes) {
            $includes->add($additionalIncludes);
        }

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
        list($page, $includes, $sort, $filters) = $this->requestParams->getFullPagination();

        $includes->add($additionalIncludes);

        return $this->repo->paginateAll($page, $includes, $sort, $filters);
    }

    /**
     * Returns all Entitys with optional Filtering, Sorting, and Includes
     *
     * @param  array $additionalIncludes
     * @return array
     */
    public function all($additionalIncludes = [])
    {
        list(, $includes, $sort, $filters) = $this->requestParams->getFullPagination();

        $includes->add($additionalIncludes);

        return $this->repo->all($includes, $sort, $filters);
    }

    /**
     * Get a JSON API Item string
     *
     * @param  array           $data
     * @param  callable|string $transformer
     * @param  string          $resourceKey
     * @param  array           $includes
     * @return string
     */
    public function getItemJson($data, $transformer, $resourceKey, $includes = [])
    {
        $resource = new Item($data, $transformer, $resourceKey);
        $manager  = new Manager;

        $manager->setSerializer(new JsonApiSerializer());
        $manager->parseIncludes($includes);

        $jsonEncodedObject = $manager->createData($resource)->toJson();

        return $jsonEncodedObject;
    }

    /**
     * Get a JSON API Collection string
     *
     * @param  array           $data
     * @param  callable|string $transformer
     * @param  string          $resourceKey
     * @param  array           $includes
     * @return string
     */
    public function getCollectionJson($data, $transformer, $resourceKey, $includes = [])
    {
        $resource = new Collection($data, $transformer, $resourceKey);
        $manager  = new Manager;

        $manager->setSerializer(new JsonApiSerializer());
        $manager->parseIncludes($includes);

        $jsonEncodedObject = $manager->createData($resource)->toJson();

        return str_replace("'", "&lsquo;", $jsonEncodedObject);
    }
}
