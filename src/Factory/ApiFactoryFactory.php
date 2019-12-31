<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Documentation\ApiFactory;

class ApiFactoryFactory
{
    /**
     * @param ContainerInterface $container
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
