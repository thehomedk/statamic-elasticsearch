<?php

namespace TheHome\StatamicElasticsearch;

use Statamic\Search\Index as BaseIndex;
use TheHome\StatamicElasticsearch\SearchTransformers;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Site;

class Index extends BaseIndex
{
    const DRIVER_NAME = "elasticsearch";

    /**
     * client
     *
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * elastic_pagination
     *
     * @var bool
     */
    protected $elastic_pagination;

    /**
     * __construct
     *
     * @param  \Elasticsearch\Client $client
     * @param  string $name
     * @param  array $config
     * @return void
     */
    public function __construct(
        \Elasticsearch\Client $client,
        string $name,
        array $config
    ) {
        $this->client = $client;
        parent::__construct($name, $config);
    }

    /**
     * useElasticPagination
     *
     * @return \TheHome\StatamicElasticsearch\Index
     */
    public function useElasticPagination(): self
    {
        $this->elastic_pagination = true;

        return $this;
    }

    /**
     * isUsingElasticPagination
     *
     * @return bool
     */
    public function isUsingElasticPagination(): bool
    {
        return (bool) $this->elastic_pagination;
    }

    /**
     * search
     *
     * @param  string $query
     * @return \TheHome\StatamicElasticsearch\Query
     */
    public function search($query): Query
    {
        return (new Query($this))->query($query);
    }

    /**
     * delete
     *
     * @param  mixed $document
     * @return void
     */
    public function delete($document): void
    {
        $params = $this->indexName();
        $params["id"] = $document->reference();
        $this->client->delete($params);
    }

    public function exists(): bool
    {
        return $this->client->indices()->exists($this->indexName());
    }

    protected function insertDocuments(
        \Statamic\Search\Documents $documents
    ): void {
        if (!$this->exists()) {
            $this->createIndex();
        }

        $transforms = $this->config["transforms"] ?? [];
        $transformers = SearchTransformers::resolve();

        $chunks = $documents->chunk(10);

        foreach ($chunks as $chunk) {
            $params = [];
            $chunk->each(function ($item, $key) use (
                &$params,
                $transforms,
                $transformers
            ) {
                foreach ($transforms as $fieldName => $funcName) {
                    if (
                        !empty($transformers[$funcName]) &&
                        !empty($item[$fieldName])
                    ) {
                        $item[$fieldName] = $transformers[$funcName](
                            $item[$fieldName]
                        );
                    }
                }

                $params["body"][] = [
                    "index" => [
                        "_index" => $this->name(),
                        "_id" => $key,
                    ],
                ];

                // Use handle from site object to filter on site
                if (isset($item["site"])) {
                    $item["site"] = $item["site"]->handle();
                }

                $params["body"][] = $item;
            });

            $this->client->bulk($params);
        }
    }

    protected function deleteIndex(): void
    {
        if ($this->client->indices()->exists($this->indexName())) {
            $this->client->indices()->delete($this->indexName());
        }
    }

    /**
     * searchUsingApi
     *
     * @param  string $query
     * @param  int $limit
     * @param  int $offset
     * @param  string $site
     * @return array
     */
    public function searchUsingApi(
        string $query,
        $limit,
        int $offset = 0,
        $site
    ): array {
        $use_status_filter = in_array("status", $this->config["fields"]);
        $use_site_filter = $site && in_array("site", $this->config["fields"]);
        $fields = array_diff($this->config["fields"], ["status", "site"]);

        $limit = $limit ?? 200;

        // Don't paginate with elasticsearch
        if (!$this->isUsingElasticPagination()) {
            $offset = 0;
            $limit = 200;
        }

        $params = $this->indexName();
        $params["body"] = [
            "from" => $offset,
            "size" => $limit,
            "_source" => false,
            "query" => [
                "bool" => [
                    "filter" => [],
                    "must" => [
                        "multi_match" => [
                            "query" => $query,
                            "fields" => $fields,
                        ],
                    ],
                ],
            ],
        ];

        if ($use_site_filter) {
            $params["body"]["query"]["bool"]["filter"][] = [
                "term" => [
                    "site" => $site,
                ],
            ];
        }

        if ($use_status_filter) {
            $params["body"]["query"]["bool"]["filter"][] = [
                "term" => [
                    "status" => "published",
                ],
            ];
        }

        $response = $this->client->search($params);

        $hits = collect($response["hits"]["hits"])->map(function ($hit) {
            $hit["id"] = $hit["_id"];
            $hit["search_score"] = $hit["_score"];

            return $hit;
        });

        return [
            "total" => $response["hits"]["total"]["value"],
            "hits" => $hits,
        ];
    }

    protected function indexName(): array
    {
        return ["index" => $this->name()];
    }

    protected function createIndex(): void
    {
        $params = $this->indexName();
        $params["body"] = [
            "settings" => [
                "analysis" => [
                    "analyzer" => [
                        "default" => [
                            "type" => $this->config["analyzer"] ?? "standard",
                        ],
                    ],
                ],
            ],
            "mappings" => [
                "properties" => [
                    "status" => ["type" => "keyword"],
                ],
            ],
        ];

        $this->client->indices()->create($params);
    }
}
