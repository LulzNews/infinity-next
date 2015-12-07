<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group([
	'prefix' => '/',
], function () {
	
	/*
	| Index route
	*/
	Route::get('/', 'WelcomeController@getIndex');
	
	Route::controller('boards.html',    'BoardlistController');
	
	Route::get('overboard.html', 'MultiboardController@getOverboard');
	
	
	/*
	| Control Panel (cp)
	| Anything having to deal with secure information goes here.
	| This includes:
	| - Registration, Login, and Account Recovery.
	| - Contributor status.
	| - Board creation, Board management, Volunteer management.
	| - Top level site management.
	*/
	Route::group([
		'namespace'  => 'Panel',
		'middleware' => 'App\Http\Middleware\VerifyCsrfToken',
		'prefix'     => 'cp',
	], function()
	{
		// Simple /cp/ requests go directly to /cp/home
		Route::get('/', 'HomeController@getIndex');
		
		Route::controllers([
			// /cp/auth handles sign-ins and registrar work.
			'auth'     => 'AuthController',
			// /cp/home is a landing page.
			'home'     => 'HomeController',
			// /cp/password handles password resets and recovery.
			'password' => 'PasswordController',
		]);
		
		// /cp/donate is a Stripe cashier system for donations.
		if (env('CONTRIB_ENABLED', false))
		{
			Route::controller('donate', 'DonateController');
		}
		
		
		// /cp/adventure forwards you to a random board.
		Route::controller('adventure', 'AdventureController');
		
		// /cp/histoy/ip will show you post history for an address.
		Route::get('history/{ip}', 'HistoryController@getHistory');
		
		Route::group([
			'prefix'    => 'bans',
		], function()
		{
			Route::get('banned',              'BansController@getIndexForSelf');
			Route::get('board/{board}/{ban}', 'BansController@getBan');
			Route::put('board/{board}/{ban}', 'BansController@putAppeal');
			Route::get('global/{ban}',        'BansController@getBan');
			Route::get('board/{board}',       'BansController@getBoardIndex');
			Route::get('global',              'BansController@getGlobalIndex');
			Route::get('/',                   'BansController@getIndex');
		});
		
		Route::group([
			'namespace' => 'Boards',
			'prefix'    => 'boards',
		], function()
		{
			Route::get('/', 'BoardsController@getIndex');
			
			Route::get('assets', 'BoardsController@getAssets');
			Route::get('config', 'BoardsController@getConfig');
			Route::get('staff',  'BoardsController@getStaff');
			Route::get('tags',   'BoardsController@getTags');
			
			Route::get('create', 'BoardsController@getCreate');
			Route::put('create', 'BoardsController@putCreate');
			
			
			Route::controller('appeals', 'AppealsController');
			Route::controller('reports', 'ReportsController');
			
			Route::group([
				'prefix'    => 'report',
			], function()
			{
				Route::get('{report}/dismiss',     'ReportsController@getDismiss');
				Route::get('{report}/dismiss-ip',  'ReportsController@getDismissIp');
				Route::get('{post}/dismiss-post',  'ReportsController@getDismissPost');
				Route::get('{report}/promote',     'ReportsController@getPromote');
				Route::get('{post}/promote-post',  'ReportsController@getPromotePost');
				Route::get('{report}/demote',      'ReportsController@getDemote');
				Route::get('{post}/demote-post',   'ReportsController@getDemotePost');
			});
		});
		
		Route::group([
			'namespace' => 'Boards',
			'prefix'    => 'board',
		], function()
		{
			Route::controllers([
				'{board}/staff/{user}' => 'StaffingController',
				'{board}/staff'        => 'StaffController',
				'{board}/role/{role}'  => 'RoleController',
				'{board}/roles'        => 'RolesController',
				'{board}'              => 'ConfigController',
			]);
		});
		
		Route::group([
			'namespace' => 'Site',
			'prefix'    => 'site',
		], function()
		{
			Route::get('/', 'SiteController@getIndex');
			Route::get('phpinfo', 'SiteController@getPhpinfo');
			
			Route::controllers([
				'config' => 'ConfigController',
			]);
		});
		
		Route::group([
			'namespace' => 'Users',
			'prefix'    => 'users',
		], function()
		{
			Route::get('/', 'UsersController@getIndex');
		});
		
		Route::group([
			'namespace' => 'Roles',
			'prefix'    => 'roles',
		], function()
		{
			Route::controller('permissions/{role}', 'PermissionsController');
			Route::get('permissions', 'RolesController@getPermissions');
		});
		
	});
	
	
	/*
	| Page Controllers
	| Catches specific strings to route to static content.
	*/
	if (env('CONTRIB_ENABLED', false))
	{
		Route::get('contribute', 'PageController@getContribute');
		Route::get('contribute.json', 'API\PageController@getContribute');
	}
	
	/*
	| API
	*/
	Route::group([
		'namespace' => "API",
	], function()
	{
		Route::get('board-details.json',  'BoardlistController@getDetails');
		Route::post('board-details.json', 'BoardlistController@getDetails');
	});
	
	
	/*
	| Board (/anything/)
	| A catch all. Used to load boards.
	*/
	Route::group([
		'prefix'    => '{board}',
	], function()
	{
		/*
		| Board Attachment Routes (Files)
		*/
		Route::group([
			'prefix'     => 'file',
			'middleware' => 'App\Http\Middleware\FileFilter',
			'namespace'  => 'Content',
		], function()
		{
			Route::get('{hash}/{filename}', 'ImageController@getImage')
				->where([
					'hash' => "[a-f0-9]{32}",
				]);
			
			Route::get('thumb/{hash}/{filename}', 'ImageController@getThumbnail')
				->where([
					'hash' => "[a-f0-9]{32}",
				]);
		});
		
		
		/*
		| Board API Routes (JSON)
		*/
		Route::group([
			'namespace' => "API\Board",
		], function()
		{
			// Gets the first page of a board.
			Route::any('index.json', 'BoardController@getIndex');
			
			// Gets index pages for the board.
			Route::get('page/{id}.json', 'BoardController@getIndex');
			
			// Gets all visible OPs on a board.
			Route::any('catalog.json', 'BoardController@getCatalog');
			
			// Gets all visible OPs on a board.
			Route::any('config.json', 'BoardController@getConfig');
			
			// Put new thread
			Route::put('thread.json', 'BoardController@putThread');
			
			// Put reply to thread.
			Route::put('thread/{post_id}.json', 'BoardController@putThread');
			
			// Get single thread.
			Route::get('thread/{post_id}.json', 'BoardController@getThread');
			
			// Get single post.
			Route::get('post/{post_id}.json', 'BoardController@getPost');
		});
		
		/*
		| Legacy API Routes (JSON)
		*/
		if (env('LEGACY_ROUTES', false))
		{
			Route::group([
				'namespace' => "API\Legacy",
			], function()
			{
				// Gets the first page of a board.
				Route::any('index.json', 'BoardController@getIndex');
				
				// Gets index pages for the board.
				Route::get('{id}.json', 'BoardController@getIndex');
				
				// Gets all visible OPs on a board.
				Route::any('threads.json', 'BoardController@getThreads');
				
				// Get single thread.
				Route::get('res/{post_id}.json', 'BoardController@getThread');
			});
		}
		
		
		/*
		| Post History
		*/
		Route::get('history/{post_id}', 'Panel\HistoryController@getBoardHistory');
		
		
		/*
		| Board Routes (Standard Requests)
		*/
		Route::group([
			'namespace' => 'Board',
		], function()
		{
			/*
			| Legacy Redirects
			*/
			if (env('LEGACY_ROUTES', false))
			{
				Route::any('index.html', function(App\Board $board) {
					return redirect("{$board->board_uri}");
				});
				Route::any('catalog.html', function(App\Board $board) {
					return redirect("{$board->board_uri}/catalog");
				});
				Route::any('{id}.html', function(App\Board $board, $id) {
					return redirect("{$board->board_uri}/{$id}");
				});
				Route::any('res/{id}.html', function(App\Board $board, $id) {
					return redirect("{$board->board_uri}/thread/{$id}");
				});
				Route::any('res/{id}+{last}.html', function(App\Board $board, $id, $last) {
					return redirect("{$board->board_uri}/thread/{$id}/{$last}");
				})->where(['last' => '[0-9]+']);
			}
			
			
			/*
			| Board Post Routes (Modding)
			*/
			Route::group([
				'prefix' => 'post/{post_id}',
			], function()
			{
				Route::controller('', 'PostController');
			});
			
			
			/*
			| Board Controller Routes
			| These are greedy and will redirect before others, so make sure they stay last.
			*/
			// Get stylesheet
			Route::get('style.css', 'BoardController@getStylesheet');
			Route::get('style.txt', 'BoardController@getStylesheetAsText');
			
			// Pushes simple /board/ requests to their index page.
			Route::any('/', 'BoardController@getIndex');
			
			// Get the catalog.
			Route::get('catalog', 'BoardController@getCatalog');
			
			// Get the config.
			Route::get('config', 'BoardController@getConfig');
			
			// Get moderator logs
			Route::get('logs', 'BoardController@getLogs');
			
			
			// Put new thread
			Route::put('thread', 'BoardController@putThread');
			
			// Generate post preview.
			Route::any('post/preview', 'PostController@anyPreview');
			
			// Check if a file exists.
			Route::get('check-file', 'BoardController@getFile');
			
			// Handle a file upload.
			Route::post('upload-file', 'BoardController@putFile');
			
			
			// Routes /board/1 to an index page for a specific pagination point.
			Route::get('{id}', 'BoardController@getIndex');
			
			// Get single thread.
			Route::get('thread/{post_id}', 'BoardController@getThread');
			Route::get('thread/{post_id}/{splice}', 'BoardController@getThread');
			
			// Redirect to a post.
			Route::get('redirect/{post_id}', 'BoardController@getThreadRedirect');
			Route::get('post/{post_id}', 'BoardController@getThread');
			
			// Put reply to thread.
			Route::put('thread/{post_id}', 'BoardController@putThread');
		});
	});
	
});

Route::group([
	'domain'     => 'static.{domain}.{tld}',
	'namespace'  => 'Content',
], function() {
	
	Route::group([
		'prefix' => "image",
	], function()
	{
		Route::get('{hash}/{filename}', 'ImageController@getImage')
			->where([
				'hash' => "[a-f0-9]{32}",
			]);
		
		Route::get('thumb/{hash}/{filename}', 'ImageController@getThumbnail')
			->where([
				'hash' => "[a-f0-9]{32}",
			]);
	});
	
});