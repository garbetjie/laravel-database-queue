# Laravel Queue: Database

A database queue driver for Laravel with optimistic locking. Heavily inspired by the article at
https://ph4r05.deadcode.me/blog/2017/12/23/laravel-queues-optimization.html, and the relevant package at
https://github.com/ph4r05/laravel-queue-database-ph4. 

## Installation

1. Install this package using composer:
```
composer require garbetjie/laravel-queue-database
```

2. Run the migration to alter the jobs table.
   If you haven't yet run the command to create the jobs table, do so first (`./artisan queue:table`).
```
php artisan garbetjie:queue:table
php artisan migrate
```

3. Replace the `database` driver in your queue connection with `optimistic`:
```php
<?php
// In config/queue.php:

return [
    'connections' => [
        'database' => [
            'driver' => 'optimistic',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 60,
            'prefetch' => 5,
            'shuffle' => true,
        ]
    ], 
];
```

## Configuration

This queue driver extends the default `database` queue driver. As a result, the configuration for this queue driver is
exactly the same as the original database queue driver (https://laravel.com/docs/7.x/queues#driver-prerequisites), except
for one additional configuration option:

| Name     | Type | Default | Description                                                                                                                               |
|----------|------|---------|-------------------------------------------------------------------------------------------------------------------------------------------|
| prefetch | int  | 5       | Determines how many queue jobs to fetch before attempting to reserve one. Should ideally default to the number of workers for your queue. |
| shuffle  | bool | true    | Whether or not to shuffle fetched jobs before attempting to reserve one.                                                                  |

