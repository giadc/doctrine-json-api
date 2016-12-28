# JSON API Request
A package for automating pagination, filtering, sorting, and includes when working with 
[Doctrine](http://www.doctrine-project.org/) and the [JSON API](http://jsonapi.org/) standard.

## Installation
`composer install giadc/doctrine-json-api`

## Basic Usage

### Using the Read Service
```php
$entityReadService->findById('id123', $includes = []);
$entityReadService->findByArray(['id123', 'id456'], 'id', $includes = []);
$entityReadService->findByField('name', 'Chiquita');
$entityReadService->paginate($includes = []);
```

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

### Filters Skeleton
```php
<?php

namespace App\Bananas\Filters;

use Giadc\DoctrineJsonApi\Filters\FilterManager;

/**
 * Class BananaFilters
 */
class BananaFilter extends FilterManager
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
