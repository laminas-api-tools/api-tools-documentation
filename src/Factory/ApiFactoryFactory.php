<?php

namespace Laminas\ApiTools\Documentation\Factory;

use interop\container\containerinterface;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Documentation\ApiFactory;

class ApiFactoryFactory
{
    /**
     * @return ApiFactory
     */
    public function __invoke(containerinterface $container)
    {
        return new ApiFactory(
            $container->get('ModuleManager'),
            $container->get('config'),
            $container->get(ModuleUtils::class)
        );
    }
}
