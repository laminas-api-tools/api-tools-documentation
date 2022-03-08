# Laminas API Tools Documentation

[![Build Status](https://github.com/laminas-api-tools/api-tools-documentation/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/laminas-api-tools/api-tools-documentation/actions/workflows/continuous-integration.yml)

> ## 🇷🇺 Русским гражданам
> 
> Мы, участники Laminas, родились и живем в разных странах. У многих из нас есть друзья, родственники и коллеги как в России, так и в Украине. Некоторые из нас родились в России. Некоторые из нас живут в России. У некоторых бабушки и дедушки сражались с фашистами во Второй мировой войне. Здесь никто не поддерживает фашизм.
> 
> У одного из нас есть украинская родственница, которая спаслась из дома вместе с сыном. Поезд задержался из-за бомбежки на дороге впереди. У нас есть друзья, которые прячутся в бомбоубежищах. Мы с тревогой ждем весточки от них после воздушных налетов, которые беспорядочно наносят удары и попадают по больницам, школам, детским садам и домам. Мы не берем это из каких-либо СМИ. Мы наблюдаем это напрямую.
> 
> Вы доверяете нам достаточно, чтоб использовать наши программы, и мы просим вас довериться нам вновь. Мы нуждаемся в помощи. Выходите и протестуйте против этой бесполезной войны. Остановите кровопролитие. Скажите "Нет войне!"
> 
> ## 🇺🇸 To Citizens of Russia
> 
> We at Laminas come from all over the world. Many of us have friends, family and colleagues in both Russia and Ukraine. Some of us were born in Russia. Some of us currently live in Russia. Some have grandparents who fought Nazis in World War II. Nobody here supports fascism.
> 
> One team member has a Ukrainian relative who fled her home with her son. The train was delayed due to bombing on the road ahead. We have friends who are hiding in bomb shelters. We anxiously follow up on them after the air raids, which indiscriminately fire at hospitals, schools, kindergartens and houses. We're not taking this from any media. These are our actual experiences.
> 
> You trust us enough to use our software. We ask that you trust us to say the truth on this. We need your help. Go out and protest this unnecessary war. Stop the bloodshed. Say "stop the war!"

## Introduction

This Laminas module can be used with conjunction with Laminas API Tools in order to:

- provide an object model of all captured documentation information, including:
  - All APIs available.
  - All _services_ available in each API.
  - All _operations_ available for each service.
  - All required/expected `Accept` and `Content-Type` request headers, and expected
    `Content-Type` response header, for each available operation.
  - All configured fields for each service.
- provide a configurable MVC endpoint for returning documentation.
  - documentation will be delivered in both HTML or serialized JSON by default.
  - end-users may configure alternate/additional formats via content-negotiation.

This module accomplishes all the above use cases by providing an endpoint to connect to
(`/api-tools/documentation[/:api[-v:version][/:service]]`), using content-negotiation to provide
both HTML and JSON representations.

## Requirements
  
Please see the [composer.json](composer.json) file.

## Installation

Run the following `composer` command:

```console
$ composer require laminas-api-tools/api-tools-documentation
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "laminas-api-tools/api-tools-documentation": "^1.2-dev"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return [
    /* ... */
    'modules' => [
        /* ... */
        'Laminas\ApiTools\Documentation',
    ],
    /* ... */
];
```

> ### laminas-component-installer
>
> If you use [laminas-component-installer](https://github.com/laminas/laminas-component-installer),
> that plugin will install api-tools-documentation as a module for you.

## Configuration

### User Configuration

This module does not utilize any user configuration.

### System Configuration

The following configuration is defined by the module to ensure operation within a Laminas
MVC application.

```php
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
        'factories' => [
            ApiFactory::class => Factory\ApiFactoryFactory::class,
        ],
    ],
    'controllers' => [
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
```

## Laminas Events

This module has no events or listeners.

## Laminas Services

### View Helpers

The following list of view helpers assist in making API documentation models presentable in view
scripts.

- `Laminas\ApiTools\Documentation\View\AgAcceptHeaders` (a.k.a `agAcceptHeaders`) for making a
  list of `Accept` headers, escaped for HTML.
- `Laminas\ApiTools\Documentation\View\AgContentTypeHeaders`  (a.k.a `agContentTypeHeaders`) for
  making a list of `Content-Type` headers, escaped for HTML.
- `Laminas\ApiTools\Documentation\View\AgServicePath` (a.k.a `agServicePath`) for making an HTML
  view representation of the route configuration of a service path.
- `Laminas\ApiTools\Documentation\View\AgStatusCodes` (a.k.a `agStatusCodes`) for making an
  escaped list of status codes and their messages.
- `Laminas\ApiTools\Documentation\View\AgTransformDescription` (a.k.a `agTransformDescription`) for transforming the written 
  descriptions into Markdown.

### Factories

#### Laminas\ApiTools\Documentation\ApiFactory

The `ApiFactory` service is capable of producing an object-graph representation of the desired
API documentation that is requested.  This object-graph will be composed of the following types:

- `Laminas\ApiTools\Documentation\Api`: the root node of an API.
- `Laminas\ApiTools\Documentation\Services`: an array of services in the API (a service can be one
  of a REST or RPC style service).
- `Laminas\ApiTools\Documentation\Operations`: an array of operations in the service.
- `Laminas\ApiTools\Documentation\Fields`: an array of fields for a service.
