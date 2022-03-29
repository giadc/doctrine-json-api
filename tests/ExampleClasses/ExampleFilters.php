<?php

namespace Giadc\DoctrineJsonApi\Tests;

use Giadc\DoctrineJsonApi\Filters\FilterManager;

class ExampleFilters extends FilterManager
{
    protected array $accepted = [
        'id'    => ['type' => 'id'],
        'name'  => ['type' => 'keyword'],
        'size'  => ['type' => 'combined', 'keys' => ['width', 'height'], 'separator' => 'x'],
        'dates' => ['type' => 'date', 'key' => 'runDate'],
        'combined' => [
            'type' => 'combined',
            'keys' => [
                'name',
                'relationships.firstName',
                'relationships.lastName',
            ],
            'separator' => ' ',
        ],
    ];
}
