<?php
namespace Apie\Serializer;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: serializer.yaml
 * @codeCoverageIgnore
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
                    $this->getTaggedServicesIterator(\Apie\Serializer\Serializer::class)
                );
                
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Serializer\Serializer::class,
            array(
              0 =>
              array(
                'name' => 'apie.context',
              ),
            )
        );
        $this->app->tag([\Apie\Serializer\Serializer::class], 'apie.context');
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
