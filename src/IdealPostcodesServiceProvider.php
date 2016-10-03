<?php namespace Squigg\IdealPostcodes;

use Illuminate\Support\ServiceProvider;

class IdealPostcodesServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/ideal-postcodes.php' => config_path('ideal-postcodes.php'),
        ]);
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        // Merge the congfiguration options
        $this->mergeConfigFrom(__DIR__.'/config/ideal-postcodes.php', 'ideal-postcodes');

        // Bind the client into the IOC
        $this->app->bind(
            'ideal-postcodes-client',
            function ($app) {
                $config = [
                    'base_uri' => config('ideal-postcodes.base_url'),
                    'timeout'  => config('ideal-postcode.timeout', 10),
                    'query'    => [
                        'api_key' => config('ideal-postcodes.api_key', 'iddqd'),
                        'limit'   => config('ideal-postcodes.limit', 25),
                        ],
                ];
                return new \GuzzleHttp\Client($config);
            }
        );

        // Bind the main class into the IOC
        $this->app->bind(
            'ideal-postcodes',
            function ($app) {
                $config = config('ideal-postcodes');
                return new IdealPostcodes($app['ideal-postcodes-client'], $config);
            }
        );

    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return ['ideal-postcodes-client', 'ideal-postcodes'];
    }

}
