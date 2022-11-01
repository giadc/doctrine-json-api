<?php

use Doctrine\Common\Collections\ArrayCollection;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;
use Giadc\DoctrineJsonApi\Tests\ExampleRelationshipEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleRepository;
use Giadc\JsonApiRequest\Requests\Filters;
use Giadc\JsonApiRequest\Requests\Includes;
use Giadc\JsonApiRequest\Requests\Pagination;
use Giadc\JsonApiRequest\Requests\RequestParams;
use Giadc\JsonApiRequest\Requests\Sorting;
use Giadc\JsonApiResponse\Pagination\PaginatedCollection;
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
        /** @var m\MockInterface&Pagination */
        $pagination = m::mock(Pagination::class);
        $pagination
            ->shouldReceive('getOffset')
            ->andReturn(0);
        $pagination
            ->shouldReceive('getPageSize')
            ->andReturn(15)
            ->getMock();

        /** @var m\MockInterface&Includes */
        $includes = m::mock(Includes::class);
        $includes->shouldReceive('add');
        $includes->shouldReceive('toArray')
            ->andReturn([])
            ->getMock();

        /** @var m\MockInterface&Sorting */
        $sorting = m::mock(Sorting::class);
        $sorting->shouldReceive('toArray')
            ->andReturn([])
            ->getMock();

        /** @var m\MockInterface&Filters */
        $filters = m::mock(Filters::class);
        $filters->shouldReceive('toArray')
            ->andReturn([])
            ->getMock();

        /** @var m\MockInterface&RequestParams */
        $params = m::mock(RequestParams::class);
        $params->shouldReceive('toArray')->andReturn([]);
        $params->shouldReceive('getIncludes')->andReturn($includes);
        $params->shouldReceive('getFiltersDetails')->andReturn($filters);
        $params->shouldReceive('getSortDetails')->andReturn($sorting);
        $params->shouldReceive('getUri')->andReturn(null);
        $params->shouldReceive('getPageDetails')->andReturn($pagination)
            ->getMock();

        $results = $this->exampleRepository->paginateAll($params);

        $this->assertInstanceOf(PaginatedCollection::class, $results);
    }

    public function test_it_finds_an_entity_by_field_value(): void
    {
        $includes = new Includes(['relationships']);
        $result   = $this->exampleRepository->findByField('Example Entity 1', 'name', $includes);

        $this->assertInstanceOf(ArrayCollection::class, $result);

        $firstResult = $result->first();

        $this->assertInstanceOf(ExampleEntity::class, $firstResult);
        $this->assertEquals('Example Entity 1', $firstResult->getName());

        /** @phpstan-ignore-next-line */
        $this->assertTrue($firstResult->getRelationships()->isInitialized());
        $this->assertEquals(2, $firstResult->getRelationships()->count());
    }

    public function test_it_finds_an_entity_by_id(): void
    {
        $includes = new Includes(['relationships']);
        $result   = $this->exampleRepository->findById('1', $includes);

        $this->assertInstanceOf(ExampleEntity::class, $result);
        $this->assertEquals('1', $result->id());

        /** @phpstan-ignore-next-line */
        $this->assertTrue($result->getRelationships()->isInitialized());
        $this->assertEquals(2, $result->getRelationships()->count());
    }

    public function test_it_finds_entities_by_array(): void
    {
        $includes = new Includes(['relationships']);
        $result   = $this->exampleRepository->findByArray(['1', '2'], 'id', $includes);

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertEquals(2, $result->count());

        /** @phpstan-ignore-next-line */
        $this->assertTrue($result->first()->getRelationships()->isInitialized());
        $this->assertInstanceOf(ExampleEntity::class, $result->first());
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
        $this->assertInstanceOf(ExampleEntity::class, $entity);

        $entity->setName('Updated Name');
        $this->exampleRepository->update($entity);
        $this->exampleRepository->clear();

        $updatedEntity = $this->exampleRepository->findById('1');
        $this->assertInstanceOf(ExampleEntity::class, $updatedEntity);
        $this->assertEquals('Updated Name', $updatedEntity->getName());
    }

    public function test_it_deletes_an_entity(): void
    {
        $entity = $this->exampleRepository->findById('1');
        $this->assertInstanceOf(ExampleEntity::class, $entity);

        $this->exampleRepository->delete($entity);
        $this->exampleRepository->clear();

        $foundEntity = $this->exampleRepository->findById('1');
        $this->assertEquals(null, $foundEntity);
    }

    public function test_it_does_not_fail_on_invalid_includes(): void
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
