# JSON API Request
A package for automating pagination, filtering, sorting, and includes when working with 
[Doctrine](http://www.doctrine-project.org/) and the [JSON API](http://jsonapi.org/) standard.


## Installation
`composer install giadc/json-api-request`


## Basic Usage

### Repository Skeleton
```php
<?php
namespace App\Bananas\Repositories;

use App\Bananas\Banana;
use App\Bananas\Filters\BananaFilter;
use App\Bananas\Repositories\BananaRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Giadc\DoctrineJsonApi\Interfaces\AbstractJsonApiRepositoryInterface as JsonApiInterface;
use Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiDoctrineRepository as JsonApiRepository;

class RoleDoctrineRepository extends JsonApiRepository implements
JsonApiInterface,
RoleRepositoryInterface
{
    /** @var EntityManager */
    protected $em;

    /** @var string */
    protected $class;
    
    /** @var BananaFilter **/
    protected $filters;

    /**
     * Create a new BananaDoctrineRepository
     *
     * @param EntityManager $em
     * @param BananaFilters $filters
     * @return void
     */
    public function __construct(EntityManager $em, BananaFilter $filters)
    {
        $this->em      = $em;
        $this->class   = Role::class;
        $this->filters = $filters;
    }
}
```

### Read Service Skeleton
```php
<?php
namespace App\Bananas\Services;

use App\Common\Services\AbstractReadService;
use App\Bananas\Repositories\BananaRepositoryInterface as BananaRepo;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleReadService extends AbstractReadService
{
    public function __construct(
        BananaRepo $bananaRepo
    ) {
        $this->initialize($bananaRepo, 'Banana');
    }
```

### Using the Read Service
```php
$bananaReadService->findById('id123', $includes = []);
$bananaReadService->findByArray(['id123', 'id456'], 'id', $includes = []);
$bananaReadService->findByField('name', 'Chiquita');
$bananaReadService->paginate($includes = []);
```

### Filters Skeleton
```php
<?php

namespace App\Bananas\Filters;

use Giadc\DoctrineJsonApi\Filters\DoctrineFilterManager;

/**
 * Class BananaFilters
 */
class BananaFilter extends DoctrineFilterManager
{
    /**
     * @var array
     */
    protected $accepted = [
        'id'    => ['type' => 'id'],      // id must match exactly
        'name' => ['type' => 'keyword'], // keyword will match fuzzily
    ];
}
```
