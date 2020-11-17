<?php

namespace TheHome\StatamicElasticsearch;

use Illuminate\Support\Collection;
use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    /**
     * total
     *
     * @var int
     */
    protected $total;

    /**
     * getSearchResults
     *
     * @param  string $query
     * @return Collection
     */
    public function getSearchResults(string $query): Collection
    {
        $result = $this->index->searchUsingApi(
            $query,
            $this->limit,
            $this->offset
        );

        $this->total = $result['total'];

        return $result['hits'];
    }

    /**
     * getItems
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->getBaseItems();
    }

    /**
     * getTotal
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
