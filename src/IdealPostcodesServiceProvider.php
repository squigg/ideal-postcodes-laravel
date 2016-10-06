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
        ], 'config');
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

                if ($fields = $this->getFieldList()) {
                    $config['query']['fields'] = $fields;
                }

                return new \GuzzleHttp\Client($config);
            }
        );

        // Bind the main class into the IOC
        $this->app->singleton(
            'ideal-postcodes',
            function ($app) {
                $config = config('ideal-postcodes');
                return new IdealPostcodes($app['ideal-postcodes-client'], $config);
            }
        );

        // Add an alias so that using the full classname as a dependency will grab this version
        $this->app->alias(IdealPostcodes::class,'ideal-postcodes');

    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return ['ideal-postcodes-client', 'ideal-postcodes'];
    }

    /**
     * Return a combined array of field names, including those to be transformed
     * @return array
     */
    protected function getFieldList()
    {
        $fields = config('ideal-postcodes.fields', null);

        if ($fields) {
            $fields = array_map(function($value) {
                if (is_array($value)) {
                    return array_keys($value)[0];
                }
                return $value;
            }, $fields);
        }

        return $fields;
    }

}
