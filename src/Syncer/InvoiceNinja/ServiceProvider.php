<?php
namespace Syncer\InvoiceNinja;

use Symfony\Component\Yaml\Yaml;
use Syncer\Command\SyncToggl;
use Syncer\Toggl\ReportsClient;
use Syncer\Toggl\TogglApiClient;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
        $this->publishes([
            __DIR__.'/../../../config/toggl.php' => config_path('toggl.php'),
            __DIR__.'/../../../config/serializer/InvoiceNinja.Task.yml' => config_path('serializer/InvoiceNinja.Task.yml'),
            __DIR__.'/../../../config/serializer/Toggl.DetailedReport.yml' => config_path('serializer/Toggl.DetailedReport.yml'),
            __DIR__.'/../../../config/serializer/Toggl.TimeEntry.yml' => config_path('serializer/Toggl.TimeEntry.yml'),
            __DIR__.'/../../../config/serializer/Toggl.Workspace.yml' => config_path('serializer/Toggl.Workspace.yml'),
            __DIR__.'/../../../config/serializer/Toggl.WorkspaceUser.yml' => config_path('serializer/Toggl.WorkspaceUser.yml'),
            __DIR__.'/../../../config/serializer/Toggl.WorkspaceProject.yml' => config_path('serializer/Toggl.WorkspaceProject.yml'),
            __DIR__.'/../../../config/serializer/Toggl.WorkspaceClient.yml' => config_path('serializer/Toggl.WorkspaceClient.yml'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(
            'Syncer\Factory\SerializerFactory',
            function($app){
                $configDir = config_path('serializer');
                $cache = storage_path('cache');
                return new \Syncer\Factory\SerializerFactory(false, $configDir, $cache);
            }
        );

        $this->app->bind(
            'JMS\Serializer\Serializer',
            function($app) {
                return $app->make('Syncer\Factory\SerializerFactory')->createSerializer();
            }
        );


        $this->app->bind(
          'Syncer\Toggl\TogglApiClient',
          function($app) {
              $apiKey = config('toggl.api_key');
              $toggleBase = config('toggl.base_uri');
              $serializer = $app->make('JMS\Serializer\Serializer');

              return new TogglApiClient(new \GuzzleHttp\Client(['base_uri' => $toggleBase]), $serializer, $apiKey);
          }
        );

        $this->app->singleton(
            'Syncer\Toggl\ReportsClient',
            function($app){
                $baseUri = config('toggl.reports_base_uri');
                $serializer = $app->make('JMS\Serializer\Serializer');
                $api_key = config('toggl.api_key');

                return new ReportsClient(new \GuzzleHttp\Client(['base_uri' => $baseUri]), $serializer, $api_key);
            }
        );

        $this->app->singleton(
            'Syncer\InvoiceNinja\Client',
            function( $app) {
                $baseUri = config('invoice_ninja.base_uri');
                $client = new \GuzzleHttp\Client([
                    'base_uri' => $baseUri
                ]);
                $serializer = $app->make('JMS\Serializer\Serializer');
                $key = config('invoice_ninja.api_key');
                return new Client($client, $serializer, $key);
            }
        );


        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncToggl::class
            ]);
        }
    }
}