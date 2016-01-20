<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| HttpCache Settings
	|--------------------------------------------------------------------------
	|
	| Enable the HttpCache to cache public resources, with a shared max age (or TTL)
	| Enable ESI for edge side includes (parts that can be cached separate)
	| Set the cache to a writable dir, outside the document root.
	|
	*/
	'enabled'   => env('APP_ESI', false),
	'esi     '  => env('APP_ESI', false),
	'cache_dir' => storage_path('httpcache'),
	
	/*
	 |--------------------------------------------------------------------------
	 | Extra options
	 |--------------------------------------------------------------------------
	 |
	 | Configure the default HttpCache options. See for a list of options:
	 | http://symfony.com/doc/current/book/http_cache.html#symfony2-reverse-proxy
	 |
	 */
	'options' => [
		
	],
	
];
