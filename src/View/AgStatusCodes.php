<?php

namespace Laminas\ApiTools\Documentation\View;

use Laminas\ApiTools\Documentation\Operation;
use Laminas\View\Helper\AbstractHelper;

use function array_map;
use function implode;
use function sprintf;

class AgStatusCodes extends AbstractHelper
{
    /** @return string */
    public function __invoke(Operation $operation)
    {
        $view        = $this->getView();
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
