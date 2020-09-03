<?php

namespace Garbetjie\Laravel\DatabaseQueue;

use Garbetjie\Laravel\DatabaseQueue\Console\Command\CreateQueueTableMigration;
use Garbetjie\Laravel\DatabaseQueue\Console\Command\CreateQueueCountTableMigration;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->extend(
            'queue',
            function (QueueManager $queueManager) {
                foreach (['optimistic', 'database-garbetjie'] as $driverName) {
                    $queueManager->addConnector(
                        $driverName,
                        function () {
                            return new Connector($this->app['db']);
                        }
                    );
                }

                return $queueManager;
            }
        );

        if ($this->app->runningInConsole()) {
            $this->commands(CreateQueueTableMigration::class, CreateQueueCountTableMigration::class);
        }
    }
}
