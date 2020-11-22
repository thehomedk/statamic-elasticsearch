[![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge)](https://statamic.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/thehome/statamic-elasticsearch.svg?style=for-the-badge)](https://packagist.org/packages/thehome/statamic-elasticsearch)
<img src="logo-elastic-horizontal-color.png?raw=true" height="30">

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

## Content transformation
Statamic supports transformers for the index data, but the current implementation is not compatible with how Laravel serializes the configuration when running `php artisan optimize`. Therefore this driver has it's own system that can be configured like this:

    'public' => [
      ...
      'transforms' => ['content' => 'bardToText'],
    ],

At the moment only the `bardToText` transformer is supported. This transformer converts the ProseMirror data structure into plain text so it can be indexed. 

## Analyzers
Elasticsearch supports different text analyzers for indexing. The analyzer determines how tokenization, stop-words and stemming is handled. You can set the default analyzer used (for all fields) like this:

    'public' => [
      'driver' => 'elasticsearch',
      'searchables' => 'collection:pages',
      'fields' => ['title', 'description', 'content'],
      'analyzer' => 'danish'
    ],

If not set, the `standard` analyzer is used. See list of available language analyzers [here](https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-lang-analyzer.html).

## Updating indexes
Whenever you save an item in the Control Panel it will automatically update any appropriate indexes as this is extending the Statamic search. As expected you can update the index via command line.

    php please search:update public

## Verify indexed data
You can verify that your elaticsearch index looks correct with:

    curl 127.0.0.1:9200/_cat/indices/public?v

Or make queries directly with:

    curl 127.0.0.1:9200/public/_search?q=test | jq

## Templating
You can use the default Statamic search tag like usual:

    {{ search:results index="public"}}
        {{ if no_results }}
            <h2>No results.</h2>
        {{ else }}
            <a href="{{ url }}">
                <div>{{ title }}</div>
                <p>{{ description | truncate:180 }}</p>
            </a>
        {{ /if }}
    {{ /search:results }}

### Livewire componenent with pagination
This Livewire component supports pagination using Elasticsearch for the pagination. This requires that the fields `status` and `site` are indexed so add them to the config:

    'public' => [
      ....
      'fields' => [ ... 'status', 'site'],
    ], 

Then you need to have Livewire support for your antlers templates. The easy way is to install Jonas's addon : 

    composer require jonassiewertsen/statamic-livewire

Then make your search antlers template including:

    {{ livewire:styles }}
    {{ livewire:elasticsearch.search index="public" limit="10"}}
    {{ livewire:scripts }}  

The template can be overridden by publishing it:

    php artisan vendor:publish --tag=elasticsearch-views

### Planned features
- Facets
- set analyzer based om documents site locale

