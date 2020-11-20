<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;
use Giadc\DoctrineJsonApi\Tests\ExampleRelationshipEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleRepository;
use Giadc\JsonApiRequest\Requests\Filters;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\RequestParams;
use Giadc\JsonApiRequest\Requests\Sorting;
use Mockery as m;

class AbstractJsonApiDoctrineRepositoryTest extends \DoctrineJsonApiTestCase
{
    /** @var ExampleRepository */
    protected $exampleRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->exampleRepository = new ExampleRepository($this->getEntityManager(), new ExampleFilters());
    }

    public function test_it_returns_paginated_results(): void
    {
        $pagination = m::mock(Pagination::class)->shouldReceive('getOffset')->andReturn(0)->shouldReceive('getPageSize')->andReturn(15)->getMock();
        $includes = m::mock(Includes::class)->shouldReceive('add')->shouldReceive('toArray')->andReturn([])->getMock();
        $sorting  = m::mock(Sorting::class)->shouldReceive('toArray')->andReturn([])->getMock();
        $filters  = m::mock(Filters::class)->shouldReceive('toArray')->andReturn([])->getMock();

        $params = m::mock(RequestParams::class)
            ->shouldReceive('toArray')->andReturn([])
            ->shouldReceive('getIncludes')->andReturn($includes)
            ->shouldReceive('getFiltersDetails')->andReturn($filters)
            ->shouldReceive('getSortDetails')->andReturn($sorting)
            ->shouldReceive('getPageDetails')->andReturn($pagination)
            ->getMock();

        $results = $this->exampleRepository->paginateAll($params);

        $this->assertInstanceOf(Paginator::class, $results);
    }

    public function test_it_finds_an_entity_by_field_value(): void
    {
        $includes = new Includes(['relationships']);
        $result   = $this->exampleRepository->findByField('Example Entity 1', 'name', $includes);

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertEquals('Example Entity 1', $result->first()->getName());
        $this->assertTrue($result->first()->getRelationships()->isInitialized());
        $this->assertEquals(2, $result->first()->getRelationships()->count());
    }

    public function test_it_finds_an_entity_by_id(): void
    {
        $includes = new Includes(['relationships']);
        $result   = $this->exampleRepository->findById('1', $includes);

        $this->assertInstanceOf(ExampleEntity::class, $result);
        $this->assertEquals('1', $result->id());
        $this->assertTrue($result->getRelationships()->isInitialized());
        $this->assertEquals(2, $result->getRelationships()->count());
    }

    public function test_it_finds_entities_by_array(): void
    {
        $includes = new Includes(['relationships']);
        $result   = $this->exampleRepository->findByArray(['1', '2'], 'id', $includes);

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertEquals(2, $result->count());
        $this->assertTrue($result->first()->getRelationships()->isInitialized());
        $this->assertEquals(2, $result->first()->getRelationships()->count());
    }

    public function test_it_adds_a_new_entity_to_the_database(): void
    {
        $newEntity = new ExampleEntity('99', 'Example Entity 99');
        $this->exampleRepository->add($newEntity);
        $this->exampleRepository->clear();

        $foundEntity = $this->exampleRepository->findById('99');
        $this->assertInstanceOf(ExampleEntity::class, $foundEntity);
    }

    public function test_it_updates_an_entity(): void
    {
        $entity = $this->exampleRepository->findById('1');
        $entity->setName('Updated Name');
        $this->exampleRepository->update($entity);
        $this->exampleRepository->clear();

        $updatedEntity = $this->exampleRepository->findById('1');
        $this->assertEquals('Updated Name', $updatedEntity->getName());
    }

    public function test_it_deletes_an_entity(): void
    {
        $entity = $this->exampleRepository->findById('1');
        $this->exampleRepository->delete($entity);
        $this->exampleRepository->clear();

        $foundEntity = $this->exampleRepository->findById('1');
        $this->assertEquals(null, $foundEntity);
    }

    public function test_it_doesnt_fail_on_invalid_includes(): void
    {
        $includes = new Includes([
            'relationships',
            'relationships.parent',
            'relationships.exGirlfriend'
        ]);

        $result = $this->exampleRepository->findById('1', $includes);

        $this->assertInstanceOf(ExampleEntity::class, $result);

        foreach ($result->getRelationships() as $relationship) {
            $this->assertInstanceOf(ExampleRelationshipEntity::class, $relationship);
            $this->assertInstanceOf(ExampleEntity::class, $relationship->getParent());
        }
    }
}
