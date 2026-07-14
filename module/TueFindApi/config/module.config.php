<?php
namespace TueFindApi\Module\Configuration;

$config = [
    'controllers' => [
        'factories' => [
            'TueFindApi\Controller\ApiController' => 'TueFindApi\Controller\ApiControllerFactory',
            'TueFindApi\Controller\MltApiController' => 'TueFindApi\Controller\MltApiControllerFactory'
        ],
        'aliases' => [
            'MltApi' => 'TueFindApi\Controller\MltApiController',
            'Api' => 'TueFindApi\Controller\ApiController'
        ],
     ],
      'service_manager' => [
        'factories' => [
            'VuFindApi\Formatter\WebRecordFormatter' => 'VuFindApi\Formatter\WebRecordFormatterFactory',
        ],
    ],
    'vufind_api' => [
        'register_controllers' => [
            \TueFindApi\Controller\MltApiController::class,
        ],
    ],
     'router' => [
         'routes' => [
             'mltApiv1' => [
                 'type' => 'Laminas\Router\Http\Literal',
                 'verb' => 'get,post,options',
                 'options' => [
                      'route'    => '/api/v1/mlt',
                      'defaults' => [
                          'controller' => 'MltApi',
                          'action'     => 'similar',
                      ]
                  ],
             ],
         ],
     ],
];

return $config;
