<?php
namespace Maxcelos\ElasticSearch;

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
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->publishes([$this->configPath() => config_path('elasticsearch.php')], 'config');

        app(EngineManager::class)->extend('elastic_search', function($app) {

            $config = config('elasticsearch.config');

            return new ElasticSearchEngine(ElasticBuilder::create()
                ->setHosts([
                    "{$config['scheme']}://{$config['user']}:{$config['pass']}@{$config['host']}:{$config['port']}"
                ])
                ->build()
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'elasticsearch');
    }

    /**
     * Path to configuration file.
     *
     * @return string
     */
    public function configPath(): string
    {
        return realpath(__DIR__ . '/../install-stubs/config/elasticsearch.php');
    }
}
