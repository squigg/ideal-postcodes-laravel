<?php namespace Squigg\IdealPostcodes;

use Illuminate\Support\ServiceProvider;
use Squigg\IdealPostcodes\Transformers\Interfaces\AddressCollectionTransformer;
use Squigg\IdealPostcodes\Transformers\Interfaces\AddressTransformer;
use Squigg\IdealPostcodes\Transformers\ModelTransformer;

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
            __DIR__ . '/config/ideal-postcodes.php' => config_path('ideal-postcodes.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        // Merge the congfiguration options
        $this->mergeConfigFrom(__DIR__ . '/../config/ideal-postcodes.php', 'ideal-postcodes');

        // Bind the client into the IOC
        $this->app->bind('ideal-postcodes-client', function ($app) {
            $config = [
                'base_uri' => config('ideal-postcodes.base_url'),
                'timeout'  => config('ideal-postcode.timeout', 10),
                'query'    => [
                    'api_key' => config('ideal-postcodes.api_key', 'iddqd'),
                    'limit'   => config('ideal-postcodes.limit', 25),
                ],
            ];

            if ($fields = $this->getFieldList()) {
                $config['query']['filter'] = $fields;
            }

            return new \GuzzleHttp\Client($config);
        });

        // Bind the main class into the IOC
        $this->app->singleton('ideal-postcodes', function ($app) {

            $config = config('ideal-postcodes');
            $service = new IdealPostcodes($app['ideal-postcodes-client'], $config);

            $service->setCollectionTransformer($this->getCollectionTransformer());
            $service->setAddressTransformer($this->getAddressTransformer());

            return $service;
        });

        // Add an alias so that using the full classname as a dependency will grab this version
        $this->app->alias('ideal-postcodes', IdealPostcodes::class);

    }

    /**
     * @return AddressCollectionTransformer
     */
    protected function getCollectionTransformer()
    {
        $transformerClass = config('ideal-postcodes.collectionTransformer', null);
        return new $transformerClass;
    }

    /**
     * @return AddressTransformer
     */
    protected function getAddressTransformer()
    {
        $transformerClass = config('ideal-postcodes.modelTransformer', null);

        if (is_a($transformerClass, ModelTransformer::class, true)) {
            return $this->getDefaultAddressTransformer($transformerClass);
        }

        return new $transformerClass;

    }

    /**
     * Get an instance of the default Model address transformer using config options
     * @param $transformerClass
     * @return mixed
     */
    protected function getDefaultAddressTransformer($transformerClass)
    {
        $model = config('ideal-postcodes.modelTransformerOptions.model');
        return new $transformerClass($model, config('ideal-postcodes.modelTransformerOptions.forceFill', false));
    }

    /**
     * Return a combined array of field names, including those to be transformed
     * @return array
     */
    protected function getFieldList()
    {
        $fields = config('ideal-postcodes.fields', null);

        if ($fields && count($fields) > 0) {
            return $fields;
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return ['ideal-postcodes-client', 'ideal-postcodes'];
    }

}
