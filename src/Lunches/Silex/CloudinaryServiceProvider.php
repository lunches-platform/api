<?php

namespace Lunches\Silex;

use Cloudinary\Uploader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryServiceProvider implements ServiceProviderInterface
{
    /** @var  [] */
    protected $config;
    public function __construct($cloudName, $apiKey, $apiSecret)
    {
        $this->config = [
            'cloud_name' => $cloudName,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
        ];
    }
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $app A container instance
     */
    public function register(Container $app)
    {
        \Cloudinary::config($this->config);

        /** @noinspection OnlyWritesOnParameterInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $app['cloudinary.upload'] = $app->protect(function(UploadedFile $file) {
            return Uploader::upload($file);
        });
    }
}