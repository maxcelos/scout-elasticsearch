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

            $config = config('scout.elastic_search.config');

            return new ElasticSearchEngine(ElasticBuilder::create()
                ->setHosts([
                    "{$config['scheme']}://{$config['user']}:{$config['pass']}@{$config['host']}:{$config['port']}"
                ])
                ->build()
            );
        });
    }
}
