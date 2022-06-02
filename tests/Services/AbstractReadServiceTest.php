<?php

use Giadc\JsonApiResponse\Pagination\FractalDoctrinePaginatorAdapter;
use Doctrine\Common\Collections\ArrayCollection;
use Giadc\DoctrineJsonApi\Exceptions\EntityCannotBeFoundException;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;
use Giadc\DoctrineJsonApi\Tests\ExampleReadService;
use Giadc\DoctrineJsonApi\Tests\ExampleRepository;

class AbstractReadServiceTest extends DoctrineJsonApiTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $exampleRepository = new ExampleRepository($this->getEntityManager());
        $this->exampleReadService = new ExampleReadService($exampleRepository, new ExampleFilters());
    }

    public function test_it_finds_an_entity_by_id(): void
    {
        $result = $this->exampleReadService->findById('1', ['relationships']);
        $this->assertInstanceOf(ExampleEntity::class, $result);
        $this->assertTrue($result->getRelationships()->isInitialized());
    }

    public function test_it_throws_an_exception_when_a_find_by_id_fails(): void
    {
        $this->expectException(EntityCannotBeFoundException::class);
        $this->exampleReadService->findByIdOrFail('doesntExist');
    }

    public function test_it_returns_null_when_a_find_by_id_fails(): void
    {
        $result = $this->exampleReadService->findById('qq');
        $this->assertNull($result);
    }

    public function test_it_finds_entities_by_array(): void
    {
        $result = $this->exampleReadService->findByArray(['1', '2'], 'id', ['relationships']);
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertTrue($result->first()->getRelationships()->isInitialized());
    }

    public function test_it_returns_an_empty_array_collection_if_findByArray_is_called_with_an_empty_array(): void
    {
        $result = $this->exampleReadService->findByArray([], 'id');
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertEquals([], $result->toArray());
    }

    public function test_it_finds_entities_by_field(): void
    {
        $result = $this->exampleReadService->findByField(10, 'width', ['relationships']);
        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertTrue($result->first()->getRelationships()->isInitialized());
    }

    public function test_it_paginates_entities(): void
    {
        $result = $this->exampleReadService->paginate(['relationships']);
        $this->assertInstanceOf(FractalDoctrinePaginatorAdapter::class, $result);
        $this->assertEquals(5, $result->getCount());
    }
}
