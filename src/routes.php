<?php

Route::group([
    'middleware' => ['auth:api', 'bindings'],
    'prefix' => config('elasticsearch.routes.prefix')
], function() {

    /*
    |--------------------------------------------------------------------------------
    | Global Search route
    |--------------------------------------------------------------------------------
    */
    Route::get(config('elasticsearch.routes.global_search_url'), function (\Illuminate\Http\Request $request) {
        return ScoutElasticSearch::globalSearch(
            $request->query('query'),
            $request->query('limit') ?? 1000,
            $request->query('page') ?? 1,
            [
                'team_id' => $request->user()->current_team_id
            ]
        );
    });
});


