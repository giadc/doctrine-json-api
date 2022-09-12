<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Giadc\DoctrineJsonApi\Services\AbstractReadService;
use Giadc\DoctrineJsonApi\Tests\ExampleRepository;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;

/**
 * @phpstan-extends AbstractReadService<ExampleEntity>
 */
class ExampleReadService extends AbstractReadService
{
    public function __construct(ExampleRepository $exampleRepo)
    {
        $this->initialize($exampleRepo, ExampleEntity::class);
    }
}
