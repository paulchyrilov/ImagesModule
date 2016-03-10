<?php
namespace Images\Infrastructure;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImageRemoveAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ClientInterface $client */
        $client = $serviceLocator->get('imageService.client');

        return new ImageRemoveAdapter($client);
    }

}