<?php

namespace Illuminate\Broadcasting;

use Illuminate\Contracts\Broadcasting\Broadcaster as BroadcasterContract;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactory;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('broadcast', fn ($app) => new BroadcastManager($app));

        $this->app->alias('broadcast', BroadcastManager::class);
        $this->app->alias('broadcast', BroadcastingFactory::class);

        $this->app->singleton(BroadcasterContract::class, function ($app) {
            return $app->make('broadcast')->connection();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'broadcast',
            BroadcastManager::class,
            BroadcastingFactory::class,
            BroadcasterContract::class,
        ];
    }
}
