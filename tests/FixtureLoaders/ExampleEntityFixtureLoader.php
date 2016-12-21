<?php

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Giadc\DoctrineJsonApi\Tests\ExampleEntity;
use Giadc\DoctrineJsonApi\Tests\ExampleRelationshipEntity;

class ExampleEntityFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getEntities() as $entity) {
            $manager->persist($entity);
        }

        $manager->flush();
    }

    private function getEntities()
    {
        $entities = [];

        for ($i = 0; $i < 5; $i++) {
            $entity = new ExampleEntity("$i", "Example Entity $i");
            $entity->addRelationship(new ExampleRelationshipEntity("{$i}a"));
            $entity->addRelationship(new ExampleRelationshipEntity("{$i}b"));
            $entities[] = $entity;
        }

        return $entities;
    }
}
