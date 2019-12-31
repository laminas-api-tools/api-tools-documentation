Laminas API Tools Documentation
==========================

[![Build Status](https://travis-ci.org/laminas-api-tools/api-tools-documentation.png)](https://travis-ci.org/laminas-api-tools/api-tools-documentation)

Introduction
------------

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

Requirements
------------
  
Please see the [composer.json](composer.json) file.

Installation
------------

Run the following `composer` command:

```console
$ composer require "laminas-api-tools/api-tools-documentation:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "laminas-api-tools/api-tools-documentation": "~1.0-dev"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'Laminas\ApiTools\Documentation',
    ),
    /* ... */
);
```

Configuration
=============

### User Configuration

This module does not utilize any user configuration.

### System Configuration

The following configuration is defined by the module to ensure operation within a Laminas
MVC application.

```php
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
            'Laminas\ApiTools\Documentation\JsonModel' => array(
                'application/json',
            ),
            'Laminas\View\Model\ViewModel' => array(
                'text/html',
                'application/xhtml+xml',
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
```

Laminas Events
==========

This module has no events or listeners.

Laminas Services
============

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

### Factories

#### `Laminas\ApiTools\Documentation\ApiFactory`

The `ApiFactory` service is capable of producing an object-graph representation of the desired
API documentation that is requested.  This object-graph will be composed of the following types:

- `Laminas\ApiTools\Documentation\Api`: the root node of an API.
- `Laminas\ApiTools\Documentation\Services`: an array of services in the API (a service can be one
  of a REST or RPC style service).
- `Laminas\ApiTools\Documentation\Operations`: an array of operations in the service.
- `Laminas\ApiTools\Documentation\Fields`: an array of fields for a service.
