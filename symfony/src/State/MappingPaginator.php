<?php

namespace App\State;

use ApiPlatform\State\Pagination\PaginatorInterface;

final class MappingPaginator implements PaginatorInterface, \IteratorAggregate
{
    /**
     * @param PaginatorInterface&\Traversable $paginator
     * @param \Closure $mapFn fn(mixed $entity): mixed
     */
    public function __construct(
        private readonly PaginatorInterface $paginator,
        private readonly \Traversable $items,
        private readonly \Closure  $mapFn,
    ) {}

    public function getCurrentPage(): float { return $this->paginator->getCurrentPage(); }
    public function getItemsPerPage(): float { return $this->paginator->getItemsPerPage(); }
    public function getLastPage(): float { return $this->paginator->getLastPage(); }
    public function getTotalItems(): float { return $this->paginator->getTotalItems(); }

    public function count(): int
    {
            return $this->paginator->count();
        }

    public function getIterator(): \Traversable
    {
        foreach ($this->items as $entity) {
            yield ($this->mapFn)($entity);
        }
    }
}
