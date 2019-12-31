<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConnectUserDatabase
{
    public function handle($request, Closure $next)
    {
        throw_if(
            !$request->headers->has('databaseName'),
            ValidationException::withMessages(['databaseName' => 'Headers need to have databaseName field.'])
        );

        $this->setDatabaseConnection($request->headers->get('databaseName'));

        return $next($request);
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
