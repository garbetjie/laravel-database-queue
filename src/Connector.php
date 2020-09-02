<?php

namespace Garbetjie\Laravel\DatabaseQueue;

use Illuminate\Queue\Connectors\DatabaseConnector;

class Connector extends DatabaseConnector
{
    public function connect(array $config)
    {
        $connection = new Queue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60
        );

        if (isset($config['prefetch'])) {
            $connection->prefetch($config['prefetch']);
        }

        if (isset($config['shuffle'])) {
            $connection->shuffle($config['shuffle']);
        }

        return $connection;
    }

}
