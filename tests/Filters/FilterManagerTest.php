<?php

use Giadc\JsonApiRequest\Requests\Filters;
use Giadc\DoctrineJsonApi\Tests\ExampleFilters;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use PHPUnit\Framework\TestCase;

class FilterManagerTest extends TestCase
{
    protected ?ExampleFilters $filterManager = null;

    public function setUp(): void
    {
        $this->filterManager = new ExampleFilters();
    }

    public function test_it_returns_the_params(): void
    {
        $this->assertTrue(is_array($this->filterManager?->getParams()));
    }

    public function test_it_processes_the_filters(): void
    {
        $entityManager = EntityManagerFactory::createEntityManager();
        $qb            = $entityManager->createQueryBuilder();

        $qb->select('e')->from(ExampleEntity::class, 'e');

        $filters = new Filters([
            'id' => '123',
            'name' => 'Eduardo',
            'deleted' => 0,
            'size' => '10x10',
            'dates' => '01/01/2016-02/02/2020',
        ]);

        $result = $this->filterManager?->process($qb, $filters)->getQuery();
        $this->assertNotNull($result, 'FilterManager could not be found');

        $expectedDQL = "SELECT e FROM " . ExampleEntity::class . " e " .
            "WHERE e.id = ?1 " .
            "AND e.name LIKE ?2 " .
            "AND e.deletedAt IS NULL " .
            "AND CONCAT(e.width, 'x', e.height) LIKE ?3 " .
            "AND (e.runDate BETWEEN ?4 AND ?5)";

        $this->assertEquals($expectedDQL, $result->getDql());

        $paramArray = $result->getParameters()->map(function ($parameter) {
            $value = $parameter->getValue();

            return ($value instanceof \DateTime)
                ? $value->format('Y-m-d H:i:s')
                : $value;
        })->toArray();

        $expectedParamArray = [
            '123',
            '%Eduardo%',
            '%10x10%',
            '2016-01-01 00:00:00',
            '2020-02-02 23:59:59',
        ];
        $this->assertEquals($expectedParamArray, $paramArray);
    }

    public function test_it_separates_join_concats(): void
    {
        $entityManager = EntityManagerFactory::createEntityManager();
        $qb            = $entityManager->createQueryBuilder();

        $qb->select('e')->from(ExampleEntity::class, 'e');

        $filters = new Filters([
            // 'id' => 'asdf',
            'combined' => 'summer asdf'
        ]);

        $result = $this->filterManager?->process($qb, $filters)->getQuery();
        $this->assertNotNull($result, 'FilterManager could not be found');

        $expectedDQL = "SELECT e FROM " . ExampleEntity::class . " e " .
            "LEFT JOIN e.relationships relationships " .
            "WHERE e.name LIKE ?1 " .
            "OR CONCAT(relationships.firstName, ' ', relationships.lastName) LIKE ?1";

        $this->assertEquals($expectedDQL, $result->getDql());

        $paramArray = $result->getParameters()->map(function ($parameter) {
            $value = $parameter->getValue();

            return ($value instanceof \DateTime)
                ? $value->format('Y-m-d H:i:s')
                : $value;
        })->toArray();

        $expectedParamArray = [
            "%summer asdf%"
        ];
        $this->assertEquals($expectedParamArray, $paramArray);
    }

    public function test_it_ignores_empty_filter_values(): void {
        $entityManager = EntityManagerFactory::createEntityManager();
        $qb            = $entityManager->createQueryBuilder();

        $qb->select('e')->from(ExampleEntity::class, 'e');

        $filters = new Filters([
            'id' => '',
            'name' => '',
            'deleted' => '',
            'size' => '',
            'dates' => '',
        ]);

        $result = $this->filterManager?->process($qb, $filters)->getQuery();
        $this->assertNotNull($result, 'FilterManager could not be found');

        // No WHERE clauses
        $expectedDQL = sprintf("SELECT e FROM %s e", ExampleEntity::class);
        $this->assertEquals($expectedDQL, $result->getDql());
    }
}
