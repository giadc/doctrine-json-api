<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

abstract class DoctrineJsonApiTestCase extends TestCase
{
    protected ?EntityManager $entityManager = null;

    /**
     * Before-test setup
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->buildSchema();
        $fixtureLoader = new FixtureLoader($this->getEntityManager());
        $fixtureLoader->execute();
    }

    /**
     * Get the Entity Manager, creating it if necessary
     */
    public function getEntityManager(): EntityManager
    {
        if (is_null($this->entityManager)) {
            $this->entityManager = EntityManagerFactory::createEntityManager();
        }

        return $this->entityManager;
    }

    protected function buildSchema(): void
    {
        $schemaTool = new SchemaTool($this->getEntityManager());
        $metadatas  = $this->getEntityManager()
            ->getMetadataFactory()
            ->getAllMetadata();

        $schemaTool->createSchema($metadatas);
    }

    /**
     * End-of-test cleanup
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager?->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
