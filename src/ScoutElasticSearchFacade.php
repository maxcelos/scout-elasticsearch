<?php

namespace Maxcelos\ScoutElasticSearch;

use Illuminate\Database\Eloquent\Collection;
use \Illuminate\Support\Facades\Facade;
use Elasticsearch\ClientBuilder as ElasticBuilder;
use Laravel\Scout\Builder;
use function Symfony\Component\VarDumper\Dumper\esc;

class ScoutElasticSearchFacade extends Facade
{

    /**
     * @param string $query
     * @param int    $hitsPerPage
     * @param int    $page
     * @param array  $where
     *
     * @return array
     */
    public static function globalSearch($query, $hitsPerPage = 1000, $page = 1, $where = [])
    {
        $es = ElasticBuilder::create()
            ->setHosts(config('scout.elastic_search.hosts'))
            ->build();

        $params = [
            'body' => [
                /*
                 * Setup pagination if requested
                 *
                 */
                'from' => $hitsPerPage * ($page - 1),
                'size' => $hitsPerPage,

                /*
                 * String searched and scope
                 *
                 */
                'query' => [
                    'bool' => [
                        'must' => [
                            'query_string' => [
                                'query' => "*{$query}*"
                            ]
                        ],
                        'filter' => self::filters($where)
                    ]
                ],

                /*
                 * Highlight setup
                 *
                 */
                'highlight' => [
                    'require_field_match' => false,
                    'fields' => [
                        '*' => ['pre_tags' => ['<em>'], 'post_tags' => ['</em>']]
                    ]
                ]
            ]
        ];

        return $es->search($params)['hits']['hits'];
    }

    protected static function filters(array $args = [])
    {
        $filter = [];

        foreach ($args as $key => $value) {
            $filter[] = ['term' => [
                    $key => (string)$value
                ]
            ];
        }

        return $filter;
    }
}
