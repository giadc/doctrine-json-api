<?php

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class FixtureLoader
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute(): void
    {
        $ormExecuter = new ORMExecutor(
            $this->entityManager,
            new ORMPurger($this->entityManager)
        );

        $ormExecuter->execute([
            new ExampleEntityFixtureLoader(),
        ]);
    }
}
