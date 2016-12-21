<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Giadc\DoctrineJsonApi\Services\AbstractReadService;
use Giadc\DoctrineJsonApi\Tests\ExampleRepository;

class ExampleReadService extends AbstractReadService
{
    public function __construct(ExampleRepository $exampleRepo)
    {
        $this->initialize($exampleRepo, 'Example Entity');
    }
}
