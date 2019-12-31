<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-documentation for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-documentation/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-documentation/blob/master/LICENSE.md New BSD License
 */

return [
    'Test\\V1\\Rest\\FooBar\\Controller' => [
        'collection' => [
            'GET' => [
                'description' => 'Collection FooBar GET!',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/foo-bar"
       },
       "first": {
           "href": "/foo-bar?page={page}"
       },
       "prev": {
           "href": "/foo-bar?page={page}"
       },
       "next": {
           "href": "/foo-bar?page={page}"
       },
       "last": {
           "href": "/foo-bar?page={page}"
       }
   }
   "_embedded": {
       "foo_bar": [
           {
               "_links": {
                   "self": {
                       "href": "/foo-bar[/:foo_bar_id]"
                   }
               }
              "goober": "This is the description for goober.",
              "bergoo": ""
           }
       ]
   }
}',
            ],
            'POST' => [
                'description' => null,
                'request' => null,
                'response' => null,
            ],
            'description' => 'Some general notes about he FooBar collections',
        ],
        'entity' => [
            'GET' => [
                'description' => null,
                'request' => null,
                'response' => null,
            ],
            'PATCH' => [
                'description' => null,
                'request' => null,
                'response' => null,
            ],
            'PUT' => [
                'description' => null,
                'request' => null,
                'response' => null,
            ],
            'DELETE' => [
                'description' => null,
                'request' => null,
                'response' => null,
            ],
            'description' => 'Some general notes about he FooBar entities',
        ],
        'description' => 'Some general notes about the FooBar rest service',
    ],
    'Test\\V1\\Rest\\FooBarCollection\\Controller' => [
        'description' => 'Some general notes about the FooBarCollection rest service',
    ],
    'Test\\V1\\Rpc\\Ping\\Controller' => [
        'GET' => [
            'description' => 'Ping the API to see uptime and network lag',
            'request' => null,
            'response' => '{"ack": 123456789}',
        ],
        'description' => 'Ping the API',
    ],
];
