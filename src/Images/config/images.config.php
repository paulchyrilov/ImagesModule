<?php
namespace Images;

use Images\Infrastructure\Http\ClientFactory;
use Images\Infrastructure\ImageTransferAdapterAbstractFactory;
use Images\Infrastructure\ImageRemoveAdapterFactory;
use Zend\Stdlib\ArrayUtils;

$defaults = [
    'log' => [
        'ImagesExceptionLogger' => [
            'writers' => [
                [
                    'name' => 'Stream',
                    'options' => [
                        'stream' => APPLICATION_DIR . '/data/logs/imagesException.log',
                        'log_separator' => PHP_EOL . PHP_EOL
                    ]
                ]
            ]
        ],
    ],
    'service_manager' => [
        'factories' => [
            'imageService.client' => ClientFactory::class,
            'imageService.removeAdapter' => ImageRemoveAdapterFactory::class
        ],
        'abstract_factories' => [
            ImageTransferAdapterAbstractFactory::class
        ]
    ],
    'imageService.adapter' => [
        'default' => []
    ],
    'imageService.client' => [
        /** in seconds, timeout is used when retrieving information from remote bounded context */
        'connectionTimeout'   =>  5,  // in seconds
        'receiveTimeout'      =>  30,  // in seconds
        'headers' => [
            'content-type' => 'application/json; charset=utf-8'
        ],
        'query' => [
            //Redefine this in local config
            'apiKey' => ''
        ],
        'host' => 'http://images.travelata.ru'
    ],
];

$serviceLocatorConfig = __DIR__ . '/serviceLocator.config.php';
if (is_file($serviceLocatorConfig)) {
    $defaults = ArrayUtils::merge($defaults, require($serviceLocatorConfig), true);
}

$localConfig = __DIR__ . '/images.config.local.php';
if (is_file($localConfig)) {
    $defaults = ArrayUtils::merge($defaults, require($localConfig), true);
}

return $defaults;
