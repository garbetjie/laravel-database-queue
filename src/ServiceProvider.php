<?php

namespace Garbetjie\Laravel\DatabaseQueue;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->extend(
            'queue',
            function (QueueManager $queueManager) {
                $queueManager->addConnector(
                    'database-optimistic',
                    function () {
                        return new Connector($this->app['db']);
                    }
                );
            }
        );
    }
}
