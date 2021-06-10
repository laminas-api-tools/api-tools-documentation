<?php

namespace Laminas\ApiTools\Documentation;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\Model\ViewModel;
use ZF\Apigility\Documentation\View\AgAcceptHeaders;
use ZF\Apigility\Documentation\View\AgContentTypeHeaders;
use ZF\Apigility\Documentation\View\AgServicePath;
use ZF\Apigility\Documentation\View\AgStatusCodes;
use ZF\Apigility\Documentation\View\AgTransformDescription;

return [
    'router'                        => [
        'routes' => [
            'api-tools' => [
                'child_routes' => [
                    'documentation' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => '/documentation[/:api[-v:version][/:service]]',
                            'constraints' => [
                                'api' => '[a-zA-Z][a-zA-Z0-9_.%]+',
                            ],
                            'defaults'    => [
                                'controller' => Controller::class,
                                'action'     => 'show',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager'               => [
        // Legacy Zend Framework aliases
        'aliases'   => [
            \ZF\Apigility\Documentation\ApiFactory::class => ApiFactory::class,
        ],
        'factories' => [
            ApiFactory::class => Factory\ApiFactoryFactory::class,
        ],
    ],
    'controllers'                   => [
        // Legacy Zend Framework aliases
        'aliases'   => [
            \ZF\Apigility\Documentation\Controller::class => Controller::class,
        ],
        'factories' => [
            Controller::class => ControllerFactory::class,
        ],
    ],
    'api-tools-content-negotiation' => [
        'controllers'      => [
            Controller::class => 'Documentation',
        ],
        'accept_whitelist' => [
            Controller::class => [
                0 => 'application/vnd.swagger+json',
                1 => 'application/json',
            ],
        ],
        'selectors'        => [
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
    'view_helpers'                  => [
        'aliases'   => [
            'agacceptheaders'        => View\AgAcceptHeaders::class,
            'agAcceptHeaders'        => View\AgAcceptHeaders::class,
            'agcontenttypeheaders'   => View\AgContentTypeHeaders::class,
            'agContentTypeHeaders'   => View\AgContentTypeHeaders::class,
            'agservicepath'          => View\AgServicePath::class,
            'agServicePath'          => View\AgServicePath::class,
            'agstatuscodes'          => View\AgStatusCodes::class,
            'agStatusCodes'          => View\AgStatusCodes::class,
            'agtransformdescription' => View\AgTransformDescription::class,
            'agTransformDescription' => View\AgTransformDescription::class,

            // Legacy Zend Framework aliases
            AgAcceptHeaders::class        => View\AgAcceptHeaders::class,
            AgContentTypeHeaders::class   => View\AgContentTypeHeaders::class,
            AgServicePath::class          => View\AgServicePath::class,
            AgStatusCodes::class          => View\AgStatusCodes::class,
            AgTransformDescription::class => View\AgTransformDescription::class,
        ],
        'factories' => [
            View\AgAcceptHeaders::class        => InvokableFactory::class,
            View\AgContentTypeHeaders::class   => InvokableFactory::class,
            View\AgServicePath::class          => InvokableFactory::class,
            View\AgStatusCodes::class          => InvokableFactory::class,
            View\AgTransformDescription::class => InvokableFactory::class,
        ],
    ],
    'view_manager'                  => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
