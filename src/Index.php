<?php

namespace TheHome\StatamicElasticsearch;

use Illuminate\Support\Collection;
use Statamic\Search\Index as BaseIndex;
use TheHome\StatamicElasticsearch\SearchTransformers;

class Index extends BaseIndex
{  
  /**
   * client
   *
   * @var \Elasticsearch\Client
   */
  protected $client;
  
  const DRIVER_NAME = 'elasticsearch';
  
  /**
   * __construct
   *
   * @param  \Elasticsearch\Client $client
   * @param  string $name
   * @param  array $config
   * @return void
   */
  public function __construct(\Elasticsearch\Client $client, string $name, array $config)
  {
    $this->client = $client;
    parent::__construct($name, $config);
  }
  
  /**
   * search
   *
   * @param  string $query
   * @return Query
   */
  public function search($query) : Query 
  {
    return (new Query($this))->query($query);
  }
  
  /**
   * delete
   *
   * @param  mixed $document
   * @return void
   */
  public function delete($document) : void
  {
    $params = $this->indexName();
    $params['id'] = $document->reference();
    $this->client->delete($params);
  }

  public function exists() : bool
  {
    return $this->client->indices()->exists($this->indexName());
  }

  protected function insertDocuments(\Statamic\Search\Documents $documents) : void
  {
    if (!$this->exists()) {
      $this->createIndex();
    }

    $transforms = $this->config['transforms'] ?? [];
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
          if (!empty($transformers[$funcName]) && !empty($item[$fieldName])) {
            $item[$fieldName] = $transformers[$funcName]($item[$fieldName]);
          }
        }

        $params['body'][] = [
          'index' => [
            '_index' => $this->name(),
            '_id' => $key,
          ],
        ];
        $params['body'][] = $item;
      });

      $this->client->bulk($params);
    }
  }

  protected function deleteIndex() : void
  {
    if ($this->client->indices()->exists($this->indexName())) {
      $this->client->indices()->delete($this->indexName());
    }
  }
  
  /**
   * searchUsingApi
   *
   * @param  string $query
   * @param  array $fields
   * @return Collection
   */
  public function searchUsingApi(string $query, array $fields = null) : Collection
  {
    $params = $this->indexName();
    $params['body'] = [
      'size' => 50,
      '_source' => false,
      'query' => [
        'multi_match' => [
          'query' => $query,
          'fields' => $this->config['fields'],
        ],
      ],
    ];

    $response = $this->client->search($params);

    return collect($response['hits']['hits'])->map(function ($hit) {
      $hit['id'] = $hit['_id'];
      $hit['search_score'] = $hit['_score'];

      return $hit;
    });
  }

  protected function indexName() : array
  {
    return ['index' => $this->name()];
  }

  protected function createIndex() : void
  {
    $params = $this->indexName();
    $params['body'] = [
      "settings" => [
        "analysis" => [
          "analyzer" => [
            "default" => [
              "type" => $this->config['analyzer'] ?? 'standard',
            ],
          ],
        ],
      ],
    ];

    $this->client->indices()->create($params);
  }
}
