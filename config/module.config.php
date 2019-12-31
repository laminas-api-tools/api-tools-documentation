<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'api-tools' => array(
                'child_routes' => array(
                    'documentation' => array(
                        'type' => 'Laminas\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/documentation[/:api[-v:version][/:service]]',
                            'constraints' => array(
                                'api' => '[a-zA-Z][a-zA-Z0-9_]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Laminas\ApiTools\Documentation\Controller',
                                'action'     => 'show',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'Laminas\ApiTools\Documentation\Controller' => 'Laminas\ApiTools\Documentation\ControllerFactory',
        ),
    ),
    'api-tools-content-negotiation' => array(
        'controllers' => array(
            'Laminas\ApiTools\Documentation\Controller' => 'Documentation',
        ),
        'accept_whitelist' => array(
            'Laminas\ApiTools\Documentation\Controller' => array(
                0 => 'application/vnd.swagger+json',
                1 => 'application/json',
            ),
        ),
        'selectors' => array(
            'Documentation' => array(
                'Laminas\View\Model\ViewModel' => array(
                    'text/html',
                    'application/xhtml+xml',
                ),
                'Laminas\ApiTools\Documentation\JsonModel' => array(
                    'application/json',
                ),
            ),
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'agacceptheaders'      => 'Laminas\ApiTools\Documentation\View\AgAcceptHeaders',
            'agcontenttypeheaders' => 'Laminas\ApiTools\Documentation\View\AgContentTypeHeaders',
            'agservicepath'        => 'Laminas\ApiTools\Documentation\View\AgServicePath',
            'agstatuscodes'        => 'Laminas\ApiTools\Documentation\View\AgStatusCodes',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
