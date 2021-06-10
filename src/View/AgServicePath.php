<?php

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Operation;
use Laminas\ApiTools\Documentation\Service;
use Laminas\View\Helper\AbstractHelper;

use function preg_quote;
use function preg_replace;

class AgServicePath extends AbstractHelper
{
    /**
     * Return the URI path for a given service and operation
     *
     * @return string
     */
    public function __invoke(Service $service, Operation $operation)
    {
        $route           = $service->getRoute();
        $routeIdentifier = $service->getRouteIdentifierName();
        $entityOps       = $service->getEntityOperations();
        if (empty($routeIdentifier) || empty($entityOps)) {
            return $route;
        }

        return preg_replace('#\[/?:' . preg_quote($routeIdentifier) . '\]#', '', $route);
    }
}
