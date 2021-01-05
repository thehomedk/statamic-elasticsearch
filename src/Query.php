<?php

namespace TheHome\StatamicElasticsearch;

use Illuminate\Support\Collection;
use Statamic\Search\QueryBuilder;
use Statamic\Facades\Data;

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
     * site
     *
     * @var string $collection
     */
    protected $collection;

    /**
     * Method getSearchResults
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
            $this->collection,
        );

        $this->total = $result['total'];

        return $result['hits'];
    }

    /**
     * Method getItems
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->getBaseItems();
    }
    
    /**
     * Method getBaseItems
     *
     * @return mixed
     */
    public function getBaseItems()
    {
        $results = $this->getSearchResults($this->query);

        if (!$this->withData) {
            return new \Statamic\Data\DataCollection($results);
        }

        return $this->collect($results)
            ->map(function ($result) {
                $data = Data::find($result['id']);
                $data->search_score = $result['search_score'];
                return $data;
            })
            ->filter()
            ->values();
    }

    /**
     * Method getTotal
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Method site
     *
     * @param  string $site
     * @return \TheHome\StatamicElasticsearch\Query
     */
    public function site($site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Method collection
     *
     * @param  string $collection
     * @return \TheHome\StatamicElasticsearch\Query
     */
    public function collection($collection): self
    {
        $this->collection = $collection;

        return $this;
    }
}
