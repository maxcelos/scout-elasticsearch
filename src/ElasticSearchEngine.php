<?php

namespace Maxcelos\ScoutElasticSearch;

use Laravel\Scout\Engines\Engine;
use Elasticsearch\Client;
use Laravel\Scout\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElasticSearchEngine extends Engine
{
    /**
     * The ElasticSearch client.
     *
     * @var \Elasticsearch\Client
     */
    protected $elastic;

    /**
     * Create a new engine instance.
     *
     * @param  \Elasticsearch\Client  $elastic
     * @return void
     */
    public function __construct(Client $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        $prefix = config('scout.prefix');

        $index = $models->first()->searchableAs();

        if ($this->usesSoftDelete($models->first()) && config('scout.soft_delete', false)) {
            $models->each->pushSoftDeleteMetadata();
        }

        $body = $models->map(function ($model) use ($prefix) {
            $array = array_merge(
                $model->toSearchableArray(), $model->scoutMetadata()
            );

            if (empty($array)) {
                return;
            }

            return array_merge([
                'objectID' => $model->getScoutKey(),
                '_scout_prefix' => $prefix,
                '_class' => get_class($model)
            ], $array);
        });

        foreach ($body as $item) {
            $test = $this->elastic->index([
                'index' => $index,
                'type' => $index,
                'id' => $item['objectID'],
                'body' => $item
            ]);
        }
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        $ids = $models->map(function ($model) {
            return $model->getScoutKey();
        })->values()->all();

        foreach ($ids as $id) {
            $this->elastic->delete([
                'index' => $models->first()->searchableAs(),
                'type' => $models->first()->searchableAs(),
                'id' => $id
            ]);
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'filters' => $this->filters($builder),
            'hitsPerPage' => $builder->limit
        ]));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, [
            'filters' => $this->filters($builder),
            'hitsPerPage' => $perPage,
            'page' => $page - 1,
        ]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  array  $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $builder->query,
                $options
            );
        }

        $params = [
            'index' => $builder->model->searchableAs(), // Ex: 'local_customers'

            'body' => [
                /*
                 * Setup pagination if requested
                 *
                 */
                'from' => isset($options['page']) ?  $options['hitsPerPage'] * $options['page'] : 0,
                'size' => isset($options['hitsPerPage']) ? $options['hitsPerPage'] : 1000,

                /*
                 * String searched and scope
                 *
                 */
                'query' => [
                    'bool' => [
                        'must' => [
                            'query_string' => [
                                'query' => "*{$builder->query}*"
                            ]
                        ],
                        'filter' => isset($options['filters']) ? $options['filters'] : []
                    ]
                ],

                /*
                 * Highlight setup
                 *
                 */
                'highlight'=> [
                    'require_field_match'=> false,
                    'fields'=> [
                        '*' => [ 'pre_tags' => ['<em>'], 'post_tags' => ['</em>'] ]
                    ]
                ]
            ]
        ];

        //dd($this->elastic->info());

        return $this->elastic->search($params);
    }

    /**
     * Get the filter array for the query.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return array
     */
    protected function filters(Builder $builder)
    {
        $filter = [];

        foreach ($builder->wheres as $key => $value) {
            $filter[] = ['term' => [
                $key => (string)$value
                ]
            ];
        }

        return $filter;
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map($results, $model)
    {
        $hits = $results['hits']['hits'];

        if (count($hits) === 0) {
            return Collection::make();
        }

        $builder = in_array(SoftDeletes::class, class_uses_recursive($model))
                    ? $model->withTrashed() : $model->newQuery();

        $models = $builder->whereIn(
            $model->getQualifiedKeyName(),
            collect($hits)->pluck('_id')->values()->all()
        )->get()->keyBy($model->getKeyName());

        return Collection::make($hits)->map(function ($hit) use ($models) {
            $key = $hit['_id'];

            if (isset($models[$key])) {
                return $models[$key];
            }
        })->filter()->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }

    /**
     * Determine if the given model uses soft deletes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function usesSoftDelete($model)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }
}
