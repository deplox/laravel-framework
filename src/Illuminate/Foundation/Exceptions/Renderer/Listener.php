<?php

namespace Illuminate\Foundation\Exceptions\Renderer;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class Listener
{
    /**
     * The queries that have been executed.
     *
     * @var array<int, array{connectionName: string, time: float, sql: string, bindings: array}>
     */
    protected $queries = [];

    /**
     * Register the appropriate listeners on the given event dispatcher.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function registerListeners(Dispatcher $events)
    {
        $events->listen(QueryExecuted::class, $this->onQueryExecuted(...));

        $events->listen([JobProcessing::class, JobProcessed::class], function () {
            $this->queries = [];
        });

        if (isset($_SERVER['LARAVEL_OCTANE'])) {
            $events->listen([
                \Laravel\Octane\Events\RequestReceived::class,
                \Laravel\Octane\Events\TaskReceived::class,
                \Laravel\Octane\Events\TickReceived::class,
                \Laravel\Octane\Events\RequestTerminated::class,
            ], function () {
                $this->queries = [];
            });
        }
    }

    /**
     * Returns the queries that have been executed.
     *
     * @return array<int, array{connectionName: string, time: float, sql: string, bindings: array}>
     */
    public function queries()
    {
        return $this->queries;
    }

    /**
     * Listens for the query executed event.
     *
     * @param  \Illuminate\Database\Events\QueryExecuted  $event
     * @return void
     */
    public function onQueryExecuted(QueryExecuted $event)
    {
        if (count($this->queries) === 101) {
            return;
        }

        $this->queries[] = [
            'connectionName' => $event->connectionName,
            'time' => $event->time,
            'sql' => $event->sql,
            'bindings' => $event->connection->prepareBindings($event->bindings),
        ];
    }
}
