<?php

namespace Garbetjie\Laravel\DatabaseQueue\Console\Command;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Queue\Console\TableCommand;
use function str_replace;

class CreateQueueTableMigration extends TableCommand
{
    /**
     * @var string
     */
    protected $signature = 'garbetjie:queue:table';

    /**
     * @var string
     */
    protected $description = 'Create the migration for enabling optimistic locking on the queue table.';

    /**
     * @param string $table
     * @return string
     */
    protected function createBaseMigration($table = 'jobs')
    {
        return $this->laravel['migration.creator']->create(
            'enable_optimistic_locking_on_'.$table.'_table', $this->laravel->databasePath().'/migrations'
        );
    }

    /**
     * @param string $path
     * @param string $table
     * @param string $tableClassName
     * @throws FileNotFoundException
     */
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
