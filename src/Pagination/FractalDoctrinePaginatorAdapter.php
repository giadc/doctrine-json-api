<?php

declare(strict_types=1);

namespace Giadc\DoctrineJsonApi\Pagination;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Giadc\JsonApiRequest\Requests\RequestParams;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * @phpstan-template Entity of \Giadc\JsonApiResponse\Interfaces\JsonApiResource
 */
final class FractalDoctrinePaginatorAdapter implements PaginatorInterface
{
    private int $total;
    private RequestParams $request;
    private string|null $url;

    /**
     * Create a new doctrine pagination adapter.
     * @phpstan-param Paginator<Entity> $paginator
     */
    public function __construct(Paginator $paginator, RequestParams $requestParams)
    {
        $this->request = $requestParams;
        $this->total = count($paginator);
        $this->url = $this->request->getUri();
    }

    /**
     * Get the current page.
     */
    public function getCurrentPage(): int
    {
        $pageDetails = $this->request->getPageDetails();
        return $pageDetails->getPageNumber();
    }

    /**
     * Get the last page.
     */
    public function getLastPage(): int
    {
        $paginator = $this->request->getPageDetails();
        $resultsPerPage = $paginator->getPageSize();

        return (int) ceil($this->getTotal() / $resultsPerPage);
    }

    /**
     * Get the total.
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get the count.
     */
    public function getCount(): int
    {
        if ($this->getPerPage() > $this->getTotal())
            return $this->getTotal();

        return $this->getPerPage();
    }

    /**
     * Get the number per page.
     */
    public function getPerPage(): int
    {
        $paginator = $this->request->getPageDetails();
        return $paginator->getPageSize();
    }

    /**
     * Get the url for the given page.
     */
    public function getUrl(int $page): string
    {
        $params = $this->request->getQueryString($page);
        return $this->url . '?' . $params;
    }

    public function __serialize(): array 
    {
        return [
            'total' => $this->total,
            'url' => $this->url,
            ...$this->request->getPageDetails()->getParamsArray(),
            ...$this->request->getIncludes()->getParamsArray(),
            ...$this->request->getSortDetails()->getParamsArray(),
            'filters' => $this->request->getFiltersDetails()->toArray(),
            'fields' => $this->request->getFields()->toArray(),
            'excludes' => $this->request->getExcludes()->toArray(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->total = $data['total'];
        $this->url = $data['url'];
        unset($data['total']);
        unset($data['url']);
        $this->request = RequestParams::fromArray($data);
    }
}
