<?php
namespace Giadc\DoctrineJsonApi\Tests;

use Giadc\DoctrineJsonApi\Filters\FilterManager;

class ExampleFilters extends FilterManager
{
    /**
     * @var array
     */
    protected $accepted = [
        'id'    => ['type' => 'id'],
        'name'  => ['type' => 'keyword'],
        'size'  => ['type' => 'combined', 'keys' => ['width', 'height'], 'separator' => 'x'],
        'dates' => ['type' => 'date', 'key' => 'runDate'],
    ];
}
