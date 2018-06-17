# Scout ElasticSearch Driver

<p>
<a href="https://packagist.org/packages/maxcelos/scout-elasticsearch"><img src="https://poser.pugx.org/maxcelos/scout-elasticsearch/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/maxcelos/scout-elasticsearch"><img src="https://poser.pugx.org/maxcelos/scout-elasticsearch/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/maxcelos/scout-elasticsearch"><img src="https://poser.pugx.org/maxcelos/scout-elasticsearch/license.svg" alt="License"></a>
</p>

Add support to ElasticSearch 6.2 on Laravel Scout

**Attention!** To use this package you need a running ElasticSearch server with **x-pack** installed.

### Installation

```
composer require maxcelos/scout-elasticsearch
```

**Optionally** you can publish the config file in order to change some default values

```
php artisan vendor:publish --provider="Maxcelos\ElasticSearch\ElasticSearchProvider" --tag=config
```

### Basic Usage

**1 ) Add this environment variables to your `.env` file.**

Change all values to match with your ElasticSearch server configuration, **except** the `SCOUT_DRIVER`

```
SCOUT_DRIVER=elastic_search
SCOUT_PREFIX=local_test_
ES_HOST=elastic.example.com
ES_PORT=9200
ES_SCHEME=http
ES_USER=xpack-user
ES_PASS=xpack-password
```

**2 ) Check out the Laravel Scout documentation**

You can access the [Official Scout Doc](https://laravel.com/docs/5.6/scout) to know how to index and search.

Example:
```
App\User::search('john')->where('team_id', 2)->get();
```

Possible results: 
- `John` Smith
- `John`athan Silva
- Daniel `John` Rambo
- ...etc

### Global Search

Laravel Scout provides a simple, driver based solution for adding full-text search to your Eloquent models, however, you may need to search in all areas of the application on the fly to make a nice global search on the navbar, for example.

To do such kind of search, this package brings an endpoint `api/g-search` ready to go. You can change this route in the config file.

For example, to search for "john" in all indexed models use the `query` parameter on a GET request:

```
http://example.com/api/g-search?query=john
```

**Paginate**

You may want to paginate the results. To do so, use the parameters `limit` and `page`.

```
http://example.com/api/g-search?query=john&limit=50&page=2
```

In the example above results number 51 to 100 (page 2) will be returned.

**Filters**

Just like Scout, this global search accept simple `where` clauses primarily useful for scoping search queries by a tenant ID.

By default, this package is ready to work with Laravel Spark using the user `current_team_id` as tenant. Of course you can copy the route below and create your own logic. 

```
Route::get('api/g-search', function (\Illuminate\Http\Request $request) {
    return Maxcelos\ElasticSearch\ScoutElasticSearch::globalSearch(
        $request->query('query'),
        $request->query('limit') ?? 1000,
        $request->query('page') ?? 1,
        [
            'team_id' => $request->user()->current_team_id
        ]
    );
});
```

The sample code above is placed on `vendor/maxcelos/scout-elasticsearch/src/ElasticSearchFacade.php`


### Tip

You can use the same Elastic Search server to multiple applications. Just make sure to have a unique `SCOUT_PREFIX` for each one.

Of course this is recommended only if all applications belongs to the same company, since they will share the same user and password, otherwise it can be a security issue.

### To Do

- Test with ElasticSearch Cloud

### About me

**Maxcelos** is a trademark of Marcelo Barros da Silva, independent professional.

Visit [maxcelos.com](maxcelos.com) and [maxcelos.github.io](maxcelos.github.io).
