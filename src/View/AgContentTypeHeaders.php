<?php

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Service;
use Laminas\View\Helper\AbstractHelper;

use function array_map;
use function implode;
use function sprintf;

class AgContentTypeHeaders extends AbstractHelper
{
    /**
     * Render a list group of Content-Type headers composed by the service
     *
     * @return string
     */
    public function __invoke(Service $service)
    {
        $requestContentTypes = [];
        if (! empty($service->getRequestContentTypes())) {
            $requestContentTypes = $service->getRequestContentTypes();
        }

        $view  = $this->getView();
        $types = array_map(function ($type) use ($view) {
            return sprintf('<div class="list-group-item">%s</div>', $view->escapeHtml($type));
        }, $requestContentTypes);
        return implode("\n", $types);
    }
}
