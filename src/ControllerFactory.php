<?php

namespace Laminas\ApiTools\Documentation;

use interop\container\containerinterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ControllerFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|array $options
     * @return Controller
     */
    public function __invoke(containerinterface $container, $requestedName, ?array $options = null)
    {
        $viewHelpers = $container->get('ViewHelperManager');

        return new Controller(
            $container->get(ApiFactory::class),
            $viewHelpers->get('ServerUrl'),
            $viewHelpers->get('BasePath')
        );
    }

    /**
     * @return Controller
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, Controller::class);
    }
}
