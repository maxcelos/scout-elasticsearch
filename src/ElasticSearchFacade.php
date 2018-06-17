<?php

namespace Maxcelos\ElasticSearch;

use \Illuminate\Support\Facades\Facade;
use Elasticsearch\ClientBuilder as ElasticBuilder;

class ElasticSearchFacade extends Facade
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
        $config = config('elasticsearch.config');

        $es = ElasticBuilder::create()
            ->setHosts([
                "{$config['scheme']}://{$config['user']}:{$config['pass']}@{$config['host']}:{$config['port']}"
            ])
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

        $filter[] = ['term' => [
            '_scout_prefix' => config('scout.prefix')
        ]
        ];

        return $filter;
    }
}
