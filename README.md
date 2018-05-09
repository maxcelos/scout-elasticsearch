# scout-elasticsearch
Add ElasticSearch support to Laravel Scout


```
'elastic_search' => [
	'config' => [
	    'host' => env('ES_HOST', 'localhost'),
	    'port' => env('ES_PORT', '9200'),
	    'scheme' => env('ES_SCHEME', 'http'),
	    'user' => env('ES_USER', ''),
	    'pass' => env('ES_PASS', '')
	],
],
```
