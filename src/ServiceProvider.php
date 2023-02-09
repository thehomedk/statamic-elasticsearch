<?php

namespace TheHome\StatamicElasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Facades\Search;
use TheHome\StatamicElasticsearch\Index as ElasticsearchIndex;

class ServiceProvider extends AddonServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Search::extend(
            ElasticsearchIndex::DRIVER_NAME,
            function ($app, array $config, $name) {
                $client = ClientBuilder::create()
                    ->setHosts($config["hosts"])
                    ->build();
                return new ElasticsearchIndex($client, $name, $config);
            }
        );

        $this->loadViewsFrom(__DIR__ . "/../resources/views", "elasticsearch");

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . "/../resources/views" => resource_path(
                        "views/vendor/elasticsearch"
                    ),
                ],
                "elasticsearch-views"
            );
        }

        if (class_exists("\Livewire")) {
            \Livewire::component(
                "elasticsearch.search",
                \TheHome\StatamicElasticsearch\Http\Livewire\Search::class
            );
        }
    }
}
