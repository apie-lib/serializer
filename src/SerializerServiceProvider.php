<?php
namespace Apie\Serializer;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: serializer.yaml
 * @codecoverageIgnore
 */
class SerializerServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\Serializer\Serializer::class,
            function ($app) {
                return \Apie\Serializer\Serializer::create(
                
                );
                
            }
        );
        $this->app->singleton(
            \Apie\Serializer\EncoderHashmap::class,
            function ($app) {
                return \Apie\Serializer\EncoderHashmap::create(
                
                );
                
            }
        );
        $this->app->singleton(
            \Apie\Serializer\DecoderHashmap::class,
            function ($app) {
                return \Apie\Serializer\DecoderHashmap::create(
                
                );
                
            }
        );
        
    }
}
