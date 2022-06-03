<?php

declare(strict_types=1);

namespace Giadc\DoctrineJsonApi\Pagination;

use League\Fractal\Pagination\PaginatorInterface;

/**
 * @phpstan-template TKey of int|string
 * @phpstan-template Item
 * @phpstan-implements \ArrayAccess<TKey, Item>
 * @phpstan-implements \Iterator<TKey, Item>
 */
class PaginatedCollection implements \ArrayAccess, \Iterator, PaginatorInterface, \Countable
{
    private PaginatorInterface $paginator;

    /**
     * @phpstan-var array<TKey, Item>
     */
    private array $items;

    /**
     * @phpstan-param array<TKey, Item> $items
     */
    public function __construct(
        array $items,
        PaginatorInterface $paginator
    ) {
        $this->items = $items;
        $this->paginator = $paginator;
    }

    public function paginator(): PaginatorInterface
    {
        return $this->paginator;
    }

    /**
     * Used for array access.
     */
    private int $position = 0;

    /**
     * Return this collection's items as an array.
     *
     * @phpstan-return array<TKey, Item>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Return the Pagination information for this collection.
     */
    public function pagination(): PaginatorInterface
    {
        return $this->paginator;
    }

    /**
     * {@inheritdoc}
     * @phpstan-param TKey|null $offset
     * @phpstan-param Item $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            /** @phpstan-ignore-next-line */
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     * @phpstan-param TKey $offset
     * @phpstan-return ?Item
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     * @phpstan-return ?Item
     */
    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->paginator->getCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage(): int
    {
        return $this->paginator->getCurrentPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage(): int
    {
        return (int) ceil(
            $this->paginator->getTotal() /
                $this->paginator->getPerPage()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal(): int
    {
        return $this->paginator->getTotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(): int
    {
        return $this->paginator->getCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getPerPage(): int
    {
        return $this->paginator->getPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(int $page): string
    {
        return '';
    }
}
