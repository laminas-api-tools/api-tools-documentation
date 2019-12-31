<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Documentation;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\Model\ViewModel;

return [
    'router' => [
        'routes' => [
            'api-tools' => [
                'child_routes' => [
                    'documentation' => [
                        'type' => 'segment',
                        'options' => [
                            'route'    => '/documentation[/:api[-v:version][/:service]]',
                            'constraints' => [
                                'api' => '[a-zA-Z][a-zA-Z0-9_.]+',
                            ],
                            'defaults' => [
                                'controller' => Controller::class,
                                'action'     => 'show',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        // Legacy Zend Framework aliases
        'aliases' => [
            \ZF\Apigility\Documentation\ApiFactory::class => ApiFactory::class,
        ],
        'factories' => [
            ApiFactory::class => Factory\ApiFactoryFactory::class,
        ],
    ],
    'controllers' => [
        // Legacy Zend Framework aliases
        'aliases' => [
            \ZF\Apigility\Documentation\Controller::class => Controller::class,
        ],
        'factories' => [
            Controller::class => ControllerFactory::class,
        ],
    ],
    'api-tools-content-negotiation' => [
        'controllers' => [
            Controller::class => 'Documentation',
        ],
        'accept_whitelist' => [
            Controller::class => [
                0 => 'application/vnd.swagger+json',
                1 => 'application/json',
            ],
        ],
        'selectors' => [
            'Documentation' => [
                ViewModel::class => [
                    'text/html',
                    'application/xhtml+xml',
                ],
                JsonModel::class => [
                    'application/json',
                ],
            ],
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'agacceptheaders'         => View\AgAcceptHeaders::class,
            'agAcceptHeaders'         => View\AgAcceptHeaders::class,
            'agcontenttypeheaders'    => View\AgContentTypeHeaders::class,
            'agContentTypeHeaders'    => View\AgContentTypeHeaders::class,
            'agservicepath'           => View\AgServicePath::class,
            'agServicePath'           => View\AgServicePath::class,
            'agstatuscodes'           => View\AgStatusCodes::class,
            'agStatusCodes'           => View\AgStatusCodes::class,
            'agtransformdescription'  => View\AgTransformDescription::class,
            'agTransformDescription'  => View\AgTransformDescription::class,

            // Legacy Zend Framework aliases
            \ZF\Apigility\Documentation\View\AgAcceptHeaders::class => View\AgAcceptHeaders::class,
            \ZF\Apigility\Documentation\View\AgContentTypeHeaders::class => View\AgContentTypeHeaders::class,
            \ZF\Apigility\Documentation\View\AgServicePath::class => View\AgServicePath::class,
            \ZF\Apigility\Documentation\View\AgStatusCodes::class => View\AgStatusCodes::class,
            \ZF\Apigility\Documentation\View\AgTransformDescription::class => View\AgTransformDescription::class,
        ],
        'factories' => [
            View\AgAcceptHeaders::class        => InvokableFactory::class,
            View\AgContentTypeHeaders::class   => InvokableFactory::class,
            View\AgServicePath::class          => InvokableFactory::class,
            View\AgStatusCodes::class          => InvokableFactory::class,
            View\AgTransformDescription::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
