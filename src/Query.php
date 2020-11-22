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
     * site
     *
     * @var string $site
     */
    protected $site;

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
            $this->offset,
            $this->site,
        );

        $this->total = $result['total'];

        return $result['hits'];
    }

    /**
     * getItems
     *
     * @return mixed
     */
    public function getItems()
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

    /**
     * set site
     * 
     * @param  string $site
     * @return \TheHome\StatamicElasticsearch\Query
     */
    public function site($site): self
    {
        $this->site = $site;

        return $this;
    }
}
