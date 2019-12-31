<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Operation;
use Laminas\ApiTools\Documentation\Service;
use Laminas\View\Helper\AbstractHelper;

class AgServicePath extends AbstractHelper
{
    /**
     * Return the URI path for a given service and operation
     *
     * @param  Service $service
     * @param  Operation $operation
     * @return string
     */
    public function __invoke(Service $service, Operation $operation)
    {
        $route = $service->getRoute();
        $routeIdentifier = $service->getRouteIdentifierName();
        $entityOps = $service->getEntityOperations();
        if (empty($routeIdentifier) || empty($entityOps)) {
            return $route;
        }

        return preg_replace('#\[/?:' . preg_quote($routeIdentifier) . '\]#', '', $route);
    }
}
