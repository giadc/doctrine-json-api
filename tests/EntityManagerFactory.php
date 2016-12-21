<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
    /**
     * Create an EntityManager
     *
     * @return EntityManger
     */
    public static function createEntityManager()
    {
        $paths            = [__DIR__ . '/yaml'];
        $isDevMode        = true;
        $connectionConfig = [
            'driver'   => 'pdo_sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ];

        $config = Setup::createYAMLMetadataConfiguration($paths, $isDevMode);

        return EntityManager::create($connectionConfig, $config);
    }
}
