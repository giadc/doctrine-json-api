<?php

declare(strict_types=1);

namespace Giadc\DoctrineJsonApi\Pagination;

use League\Fractal\Pagination\PaginatorInterface;

class PaginatedCollection
{
    private PaginatorInterface $paginator;
    private mixed $data;

    public function __construct(
        mixed $data,
        PaginatorInterface $paginator
    ) {
        $this->data = $data;
        $this->paginator = $paginator;
    }

    public function paginator(): PaginatorInterface
    {
        return $this->paginator;
    }

    public function data(): mixed
    {
        return $this->data;
    }
}
