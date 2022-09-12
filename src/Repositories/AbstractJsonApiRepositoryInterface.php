<?php

namespace Giadc\DoctrineJsonApi\Repositories;

/**
 * @template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 *
 * @extends ReadJsonApiRepositoryInterface<Entity>
 * @extends WriteJsonApiRepositoryInterface<Entity>
 */
interface AbstractJsonApiRepositoryInterface extends ReadJsonApiRepositoryInterface, WriteJsonApiRepositoryInterface
{
}
