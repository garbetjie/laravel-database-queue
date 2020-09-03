<?php

namespace Garbetjie\Laravel\DatabaseQueue\Console\Command;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Queue\Console\TableCommand;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use function str_replace;

class CreateQueueCountTableMigration extends Command
{
    /**
     * @var string
     */
    protected $signature = 'garbetjie:queue:create-count-table';

    /**
     * @var string
     */
    protected $description = 'Create the migration for storing queue counts in a separate table.';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @param Filesystem $files
     * @param Composer $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    public function handle()
    {
        // Table migration.
        $this->replaceMigration(
            '01_create_database_queue_counts_table',
            __DIR__ . '/queue_counts_table.stub'
        );

        // Trigger creation.
        $this->replaceMigration(
            '02_create_database_queue_counts_trigger',
            __DIR__ . '/queue_counts_trigger.stub'
        );

        // Populate the counts table.
        $this->replaceMigration(
            '03_create_database_queue_counts_insertion',
            __DIR__ . '/queue_counts_insert.stub'
        );

        $this->composer->dumpAutoloads();
    }

    protected function replaceMigration($migrationName, $stubPath)
    {
        $jobsTable = $this->laravel['config']['queue.connections.database.table'];
        $table = $jobsTable . '_count';

        $path = $this->laravel['migration.creator']->create($migrationName, $this->laravel->databasePath() . '/migrations');
        $stub = str_replace(
            ['{{table}}', '{{tableClassName}}', '{{jobsTable}}'],
            [$table, Str::studly($table), $jobsTable],
            $this->files->get($stubPath)
        );

        $this->files->put($path, $stub);
    }
}
