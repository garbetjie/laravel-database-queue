<?php

namespace Garbetjie\Laravel\DatabaseQueue;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\Query\Expression;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Support\Collection;
use stdClass;
use function mt_rand;

class Queue extends DatabaseQueue
{
    /**
     * @var int
     */
    protected $prefetch = 5;

    /**
     * Set the number of queue records to prefetch when trying to acquire a job.
     *
     * @param int $prefetch
     *
     * @return $this
     */
    public function prefetch(int $prefetch)
    {
        if ($prefetch < 1) {
            $prefetch = 1;
        }

        $this->prefetch = $prefetch;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        return ['version' => 0] + parent::buildDatabaseRecord($queue, $payload, $availableAt, $attempts);
    }

    /**
     * @param DatabaseJobRecord $job
     *
     * @return DatabaseJobRecord|null
     */
    protected function markJobAsReserved($job)
    {
        $affected = $this->database->table($this->table)
            ->where('id', $job->id)
            ->where('version', $job->version)
            ->update([
                'reserved_at' => $job->touch(),
                'attempts' => $job->increment(),
                'version' => new Expression(
                    $this->database->getQueryGrammar()->wrap('version') . ' + 1'
                )
            ]);

        return $affected > 0 ? $job : null;
    }

    /**
     * @param string $queue
     * @param DatabaseJobRecord $job
     *
     * @return DatabaseJob|void
     */
    protected function marshalJob($queue, $job)
    {
        if ($job = $this->markJobAsReserved($job)) {
            return new DatabaseJob($this->container, $this, $job, $this->connectionName, $queue);
        }
    }

    /**
     * @param string|null $queue
     *
     * @return Job|DatabaseJob|void
     */
    public function pop($queue = null)
    {
        // Get the actual queue name.
        $queue = $this->getQueue($queue);

        // Get the jobs that are available to be processed.
        $available = $this->getNextAvailableJobs($queue);

        // If there are no jobs available, then return null.
        if ($available->isEmpty()) {
            return;
        }

        // Shuffle the available jobs, and iterate over them and return the first job that can be claimed.
        $shuffled = $available->shuffle();

        foreach ($shuffled as $job) {
            if ($claimed = $this->marshalJob($queue, new DatabaseJobRecord($job))) {
                return $claimed;
            }
        }
    }

    /**
     * @param string $queue
     * @param string $id
     *
     * @return void
     */
    public function deleteReserved($queue, $id)
    {
        $this->database->table($this->table)->where('id', $id)->delete();
    }

    /**
     * @param Collection $jobs
     *
     * @return stdClass
     */
    private function selectJob($jobs)
    {
        $count = $jobs->count();

        if ($count === 1) {
            $jobs[0];
        }

        return (object)$jobs[mt_rand(0, $count - 1)];
    }

    /**
     * Returns a collection containing all the jobs that are available to be processed. The number of jobs returned is
     * limited to the `prefetch` config key.
     *
     * @param string $queue
     *
     * @return Collection
     */
    private function getNextAvailableJobs($queue)
    {
        return $this->database->table($this->table)
            ->where('queue', $queue)
            ->where(
                function ($query) {
                    $this->isAvailable($query);
                    $this->isReservedButExpired($query);
                }
            )
            ->orderBy('id', 'asc')
            ->limit($this->prefetch)
            ->get();
    }
}
