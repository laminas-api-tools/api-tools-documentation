<?php

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Service;
use Laminas\View\Helper\AbstractHelper;

use function array_map;
use function implode;
use function sprintf;

class AgAcceptHeaders extends AbstractHelper
{
    /**
     * Render a list group of Accept headers composed by the service
     *
     * @return string
     */
    public function __invoke(Service $service)
    {
        $requestAcceptTypes = $service->getRequestAcceptTypes();
        if (empty($requestAcceptTypes)) {
            $requestAcceptTypes = [];
        }

        $view  = $this->getView();
        $types = array_map(function ($type) use ($view) {
            return sprintf('<div class="list-group-item">%s</div>', $view->escapeHtml($type));
        }, $requestAcceptTypes);
        return implode("\n", $types);
    }
}
