<?php

namespace Ava\Thumbs\Providers;

use Illuminate\Support\ServiceProvider;
use Ava\Thumbs\Commands;

class ThumbsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallCommand::class,
            ]);
        }

        $this->publishes([
            dirname(__DIR__).'/../routes/thumbs.php' => base_path('routes/thumbs.php')
        ],'ava-thumbs-routes');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }


}
