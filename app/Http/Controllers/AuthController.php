<?php
namespace App\Http\Controllers;

use App\Food;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signUp(Request $request)
    {
        $randomNumber = mt_rand(1000, 9999);
        $user = request()->input('username') . $randomNumber;
        $email = "duwaljyoti16+test{$randomNumber}@gmail.com";
        $testDatabase = "playground_database_{$user}";
        // create a database

        $this->setDatabaseConnection($testDatabase);

//        dd(\config('database.connections'));
        // run migrations in the newly created database

        Artisan::call('migrate', [
            '--database' => 'mysql',
            '--path' => 'database/migrations/tenantMigrations'
        ]);

        $this->setDefaultDatabaseConnections();
        $this->createNewUser($request, $email, $testDatabase);

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    private function createNewUser($request, $email, $dbName)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);


        $user = new User([
            'name' => $request->name,
            'email' => $email,
            'password' => bcrypt($request->password),
            'db_id' => $dbName,
        ]);

        $user->save();

        dump($user);
    }

    // passport reference https://medium.com/modulr/create-api-authentication-with-passport-of-laravel-5-6-1dc2d400a7f
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
//        dd(Auth::user()->db_id);
        $dbId = Auth::user()->db_id;
        $this->setDatabaseConnection($dbId);
        $this->testCreate();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'db_id' => $dbId
        ]);
    }

    public function testCreate()
    {
        Food::create([
            'name' => 'Pizza',
            'price' => 1213,
            'description' => 'Test description.',
            'tags' => '"test tag."'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    private function setDefaultDatabaseConnections()
    {
        $this->setDatabaseConnection('foa_master_db');
    }

    private function setDatabaseConnection(string $databaseName)
    {
        // following this one.
//        https://laravel.io/forum/09-13-2014-create-new-database-and-tables-on-the-fly
        // right now having an issue with the installation because of mcrypt extension php
        // https://github.com/uxweb/laravel-multi-db
//        Artisan::call('migrate:install');

        // https://stackoverflow.com/questions/51074804/connect-multiple-databases-dynamically-in-laravel
        // https://hackernoon.com/the-ultimate-guide-for-laravel-multi-tenant-with-multi-database-779ea4592783
        // https://hackernoon.com/simple-multi-tenancy-with-laravel-b3f84fc13c39

        DB::statement("create database if not exists  {$databaseName}");

        Config::set('database.mysql.database', $databaseName);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'engine' => null,
            'strict' => true,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
    }
}
