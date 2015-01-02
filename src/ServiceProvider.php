<?php namespace SocialEngine\TestDbSetup;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use SocialEngine\TestDbSetup\Commands\SetupTestDb;

class ServiceProvider extends LaravelServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('command.setup-test-db', function($app) {
            return new SetupTestDb($app['config']);
        });
    }

    public function boot()
    {
        $this->commands('command.setup-test-db');
        $this->package('socialengine/setup-test-db', 'setup-test-db', __DIR__);
    }
}
