<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Operation;
use Laminas\View\Helper\AbstractHelper;

class AgStatusCodes extends AbstractHelper
{
    public function __invoke(Operation $operation)
    {
        $view = $this->getView();
        $statusCodes = array_map(function ($status) use ($view) {
            return sprintf(
                '<li class="list-group-item"><strong>%s:</strong> %s</li>',
                $view->escapeHtml($status['code']),
                $view->escapeHtml($status['message'])
            );
        }, $operation->getResponseStatusCodes());

        return sprintf("<ul class=\"list-group\">\n%s\n</ul>\n", implode("\n", $statusCodes));
    }
}
