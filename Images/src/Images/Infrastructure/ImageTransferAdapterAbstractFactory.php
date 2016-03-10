<?php
namespace Images\Infrastructure;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\File\Extension;
use Zend\Validator\File\MimeType;
use Zend\Validator\File\Size;

class ImageTransferAdapterAbstractFactory implements AbstractFactoryInterface
{

    private $config;

    private $defaults = [
        'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'mimetypes'  => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png'],
        'size'      => ['max' => '2MB']
    ];

    /**
     * Configuration key holding configuration
     *
     * @var string
     */
    protected $configKey = 'imageService.adapter';


    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {

        $config = $this->getConfig($serviceLocator);

        if (empty($config)) {
            return false;
        }

        $serviceConfigKey = $this->getServiceConfigKey($requestedName);

        if (!isset($config[$serviceConfigKey])) {
            return false;
        }

        return true;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {

        $serviceConfig = $this->getServiceConfig($requestedName, $serviceLocator);

        if(!is_array($serviceConfig)) {
            throw new ServiceNotCreatedException('Can\'t create service ' . $requestedName);
        }

        /** @var ClientInterface $client */
        $client = $serviceLocator->get('imageService.client');

        $adapter = new ImageTransferAdapter($client, $serviceConfig);

        $extensionValidator = new Extension($serviceConfig['extensions']);
        $adapter->addValidator($extensionValidator, true);

        $mimeTypeValidator = new MimeType($serviceConfig['mimetypes']);
        $adapter->addValidator($mimeTypeValidator, true);

        $sizeValidator = new Size($serviceConfig['size']);
        $adapter->addValidator($sizeValidator, true);

        return $adapter;
    }

    private function getServiceConfigKey($serviceName)
    {
        $nameParts = explode('.', $serviceName);

        if(count($nameParts) > 1) {
            $serviceName = array_pop($nameParts);
        }

        return $serviceName;
    }

    private function getServiceConfig($serviceName, ServiceLocatorInterface $serviceLocator)
    {
        $serviceConfigKey = $this->getServiceConfigKey($serviceName);

        $config = $this->getConfig($serviceLocator);

        if (empty($config)) {
            return false;
        }

        if (!isset($config[$serviceConfigKey])) {
            return false;
        }

        return array_merge($this->defaults, $config[$serviceConfigKey]);
    }

    /**
     * Retrieve configuration for service, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $this->config = [];

        if (!$services->has('Config')) {
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])) {
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

}