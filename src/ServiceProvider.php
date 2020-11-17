<?php

namespace TheHome\StatamicElasticsearch;

use Elasticsearch\ClientBuilder;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Search\IndexManager;
use TheHome\StatamicElasticsearch\Index as ElasticsearchIndex;

class ServiceProvider extends AddonServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        app(IndexManager::class)->extend(
            ElasticsearchIndex::DRIVER_NAME,
            function ($app, $config, $name) {
                $client = ClientBuilder::create()
                    ->setHosts($config['hosts'])
                    ->build();
                return new ElasticsearchIndex($client, $name, $config);
            }
        );
    }
}
