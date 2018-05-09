<?php
namespace Maxcelos\ScoutElasticSearch;

use \Illuminate\Support\Facades\Facade;

class ScoutElasticSearchFacade extends Facade {
    protected static function getFacadeAccessor() {
        return 'ScoutElasticSearch';
    }
}