<?php

namespace Laminas\ApiTools\Documentation\Factory;

use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Documentation\ApiFactory;
use Psr\Container\ContainerInterface;

class ApiFactoryFactory
{
    /**
     * @return ApiFactory
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ApiFactory(
            $container->get('ModuleManager'),
            $container->get('config'),
            $container->get(ModuleUtils::class)
        );
    }
}
