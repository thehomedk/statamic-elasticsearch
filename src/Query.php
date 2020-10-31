<?php

namespace TheHome\StatamicElasticsearch;

use Illuminate\Support\Collection;
use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    public function getSearchResults(string $query) : Collection
    {
        return $this->index->searchUsingApi($query);
    }
}