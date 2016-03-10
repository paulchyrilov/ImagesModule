<?php
namespace Images;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{

    public function getModuleDir()
    {
        return __DIR__;
    }

    public function getModuleNamespace()
    {
        return __NAMESPACE__;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    $this->getModuleNamespace() => $this->getModuleDir() . '/src/' . $this->getModuleNamespace(),
                ),
            ),
        );
    }

    public function getConfig()
    {
        $config = include ($this->getModuleDir() . '/config/images.config.php');
        return $config;
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
            ]
        ];
    }

}
