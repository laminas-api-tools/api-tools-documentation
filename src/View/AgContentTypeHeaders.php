<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Service;
use Laminas\View\Helper\AbstractHelper;

class AgContentTypeHeaders extends AbstractHelper
{
    /**
     * Render a list group of Content-Type headers composed by the service
     *
     * @param  Service $service
     * @return string
     */
    public function __invoke(Service $service)
    {
        $requestContentTypes = [];
        if (! empty($service->getRequestContentTypes())) {
            $requestContentTypes = $service->getRequestContentTypes();
        }

        $view = $this->getView();
        $types = array_map(function ($type) use ($view) {
            return sprintf('<div class="list-group-item">%s</div>', $view->escapeHtml($type));
        }, $requestContentTypes);
        return implode("\n", $types);
    }
}
