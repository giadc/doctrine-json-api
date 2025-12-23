<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class EntityManagerFactory
{
    /**
     * Create an EntityManager
     *
     * @return EntityManager
     */
    public static function createEntityManager()
    {
        $paths            = [__DIR__ . '/ExampleClasses'];
        $isDevMode        = true;
        $connectionConfig = [
            'driver'   => 'pdo_sqlite',
            'memory'   => true,
        ];

        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);
        $config->enableNativeLazyObjects(true);
        $connection = DriverManager::getConnection($connectionConfig, $config);

        return new EntityManager($connection, $config);
    }
}
