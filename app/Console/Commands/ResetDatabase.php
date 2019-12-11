<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears all the user database as well as the main database.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
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
        \Illuminate\Support\Facades\Artisan::call('passport:install');

        echo "<p>Performed";
    }
}
