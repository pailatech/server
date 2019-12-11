<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* @var \Illuminate\Routing\Router $router */
$router = resolve(\Illuminate\Routing\Router::class);


\Illuminate\Support\Facades\Route::group([
    'prefix' => 'auth',
], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('signup', 'AuthController@signUp');

    $router->group([
        'middleware' => 'auth:api'
    ], function () use ($router) {
        $router->get('logout', 'AuthController@logout');
        $router->get('user', 'AuthController@user');
    });
});

$router->get('delete-all-tenant-db', function () {
    $playgroundDatabases = \Illuminate\Support\Facades\DB::select('SHOW DATABASES LIKE \'%playground%\'');

    /* @var \Illuminate\Database\Schema\Builder $builder */
    $builder = resolve(\Illuminate\Database\Schema\Builder::class);

    if (count($playgroundDatabases)) {
        foreach ($playgroundDatabases as $index => $databaseObject) {
            $databaseName = get_object_vars($databaseObject)["Database (%playground%)"];
            $builder->getConnection()->getDoctrineSchemaManager()->dropDatabase($databaseName);

            echo "Database deleted: {$databaseName}";
        }
        echo "<p>Done!";
    } else {
        echo 'No playground databases found.';
    }


    \Illuminate\Support\Facades\Artisan::call('migrate:fresh');

    echo "<p>Performed";
});

$router->get('/test', function() {
   return 'here';
});

$router->group(['middleware' => \App\Http\Middleware\ConnectUserDatabase::class], function () use ($router) {
    $router->get('{entity}', CommonController::class . '@index');
    $router->post('{entity}', CommonController::class . '@store');
});
