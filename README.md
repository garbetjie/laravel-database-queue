# Laravel Database Queue Driver

A database queue driver for Laravel with optimistic locking and job count caching. Heavily inspired by the article at
https://ph4r05.deadcode.me/blog/2017/12/23/laravel-queues-optimization.html, and the relevant package at
https://github.com/ph4r05/laravel-queue-database-ph4. 

## Installation

1. Install this package using composer:
```
composer require garbetjie/laravel-database-queue
```

2. Run the migration to alter the jobs table.
   If you haven't yet run the command to create the jobs table, do so first (`./artisan queue:table`).
```
php artisan garbetjie:database-queue:table
php artisan migrate
```

3. Replace the `database` driver in your queue connection with `database-garbetjie`:
```php
<?php
// In config/queue.php:

return [
    'connections' => [
        'database' => [
            'driver' => 'database-garbetjie',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 60,
            'prefetch' => 5,
            'shuffle' => true,
        ]
    ], 
];
```

4. Optionally, you can also create a "cache" table for job counts.
   
   If you have many jobs in your queue, running a query like `SELECT queue, COUNT(*) FROM jobs GROUP BY 1` can take a
   long time to yield results. Run the following command to generate migrations that will create a job count cache table, and will keep the job counts
   updated through the use of triggers.
```
php artisan garbetjie:database-queue:table-job-counts
php artisan migrate
```

### Configuration

This queue driver extends the default `database` queue driver. As a result, the configuration for this queue driver is
exactly the same as the original database queue driver (https://laravel.com/docs/7.x/queues#driver-prerequisites), except
for some additional configuration options:

| Name     | Type | Default | Description                                                                                                                               |
|----------|------|---------|-------------------------------------------------------------------------------------------------------------------------------------------|
| prefetch | int  | 5       | Determines how many queue jobs to fetch before attempting to reserve one. Should ideally default to the number of workers for your queue. |
| shuffle  | bool | true    | Whether or not to shuffle fetched jobs before attempting to reserve one.                                                                  |

