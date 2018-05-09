<?php
namespace Maxcelos\ScoutElasticSearch;

use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder as ElasticBuilder;

class ElasticSearchProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        app(EngineManager::class)->extend('elastic_search', function($app) {
            return new ElasticSearchEngine(ElasticBuilder::create()
                ->setHosts(config('scout.elastic_search.hosts'))
                ->build(),
                config('scout.elastic_search.index')
            );
        });
    }
}
