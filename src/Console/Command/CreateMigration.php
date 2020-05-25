<?php

namespace Garbetjie\Laravel\DatabaseQueue\Console\Command;

use Illuminate\Queue\Console\TableCommand;
use function str_replace;

class CreateMigration extends TableCommand
{
    protected $signature = 'garbetjie:queue:table';

    protected $description = 'Create the migration for enabling optimistic locking on the queue table.';

    protected function createBaseMigration($table = 'jobs')
    {
        return $this->laravel['migration.creator']->create(
            'add_version_to_'.$table.'_table', $this->laravel->databasePath().'/migrations'
        );
    }


    protected function replaceMigration($path, $table, $tableClassName)
    {
        $stub = str_replace(
            ['{{table}}', '{{tableClassName}}'],
            [$table, $tableClassName],
            $this->files->get(__DIR__.'/version.stub')
        );

        $this->files->put($path, $stub);
    }
}
