<?php

namespace TheHome\StatamicElasticsearch;

use Statamic\Search\Index as BaseIndex;
use TheHome\StatamicElasticsearch\SearchTransformers;

class Index extends BaseIndex
{
  const DRIVER_NAME = 'elasticsearch';

  public function __construct($client, $name, array $config)
  {
    $this->client = $client;
    parent::__construct($name, $config);
  }

  public function search($query)
  {
    return (new Query($this))->query($query);
  }

  public function delete($document)
  {
    $params = $this->indexKey();
    $params['id'] = $document->reference();
    $this->client->delete($params);
  }

  public function exists()
  {
    return $this->client->indices()->exists($this->indexKey());
  }

  protected function insertDocuments(\Statamic\Search\Documents $documents)
  {
    if (!$this->exists()) {
      $this->createIndex();
    }

    $transforms = $this->config['transforms'];
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
            '_index' => $this->title(),
            '_id' => $key,
          ],
        ];
        $params['body'][] = $item;
      });

      $this->client->bulk($params);
    }
  }

  protected function deleteIndex()
  {
    if ($this->client->indices()->exists($this->indexKey())) {
      $this->client->indices()->delete($this->indexKey());
    }
  }

  public function searchUsingApi($query, $fields = null)
  {
    $params = $this->indexKey();
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

  protected function indexKey()
  {
    return ['index' => $this->title()];
  }

  protected function createIndex()
  {
    $params = $this->indexKey();
    $params['body'] = [
      "settings" => [
        "analysis" => [
          "analyzer" => [
            "default" => [
              "type" => "danish",
            ],
          ],
        ],
      ],
    ];

    $this->client->indices()->create($params);
  }
}
