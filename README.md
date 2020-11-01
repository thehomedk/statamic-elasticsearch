# Elasticsearch driver for Statamic

This addon add's a Elasticsearch driver to Statamics builtin search system ( [docs](https://statamic.dev/search) ).

## Setup index
Indexes are configured in `config/statamic/search.php` and uses the standard Statamic index configuration. There's a few extra nice configuration options.

Here's an entry in `indexes` for the pages collection:

    'public' => [
      'driver' => 'elasticsearch',
      'searchables' => 'collection:pages',
      'fields' => ['title', 'description', 'content'],
    ],

Here's an example entry in the `drivers` section defining the connection properties for your Elasticsearch server.

    'elasticsearch' => [
        'hosts' => [[
           'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
           'host' => env('ELASTICSEARCH_HOST', '127.0.0.1'),
           'port' => env('ELASTICSEARCH_PORT', 9200),
        ]]
    ],

## Updating indexes
Whenever you save an item in the Control Panel it will automatically update any appropriate indexes as this is extending the Statamic search. As expected you can update the index via command line.

    php please search:update public

## Verify indexed data
You can verify that your elaticsearch index looks correct with:

    curl 127.0.0.1:9200/_cat/indices/public?v

Or make queries directly with:

    curl 127.0.0.1:9200/business/_search?q=test | jq