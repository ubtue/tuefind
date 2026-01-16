<?php

namespace VuFindCollapseExpand\Module\Configuration;

$config = [
    'service_manager' => [
        'factories' => [
            \VuFindCollapseExpand\Config\CollapseExpand::class  => \VuFindCollapseExpand\Config\CollapseExpandFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            \VuFindCollapseExpand\Controller\AuthorController::class => \VuFind\Controller\AbstractBaseFactory::class,
            \VuFindCollapseExpand\Controller\SearchController::class => \VuFind\Controller\AbstractBaseFactory::class,
        ],
        'initializers' => [
            \VuFindCollapseExpand\ServiceManager\ServiceInitializer::class
        ],
        'aliases' => [
            \VuFind\Controller\AuthorController::class    => \VuFindCollapseExpand\Controller\AuthorController::class,
            \VuFind\Controller\SearchController::class    => \VuFindCollapseExpand\Controller\SearchController::class,
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'ajaxhandler' => [
                'factories' => [
                    \VuFindCollapseExpand\AjaxHandler\CollapseExpandCheckbox::class =>
                        \Laminas\ServiceManager\Factory\InvokableFactory::class,
                ],
                'initializers' => [
                    \VuFindCollapseExpand\ServiceManager\ServiceInitializer::class
                ],
                'aliases' => [
                    'collapseExpandCheckbox' => \VuFindCollapseExpand\AjaxHandler\CollapseExpandCheckbox::class,
                ]
            ],
            'recorddriver' => [
                'initializers' => [
                    \VuFindCollapseExpand\ServiceManager\ServiceInitializer::class
                ],
            ],
            'recordtab' => [
                'factories' => [
                    \VuFindCollapseExpand\RecordTab\CollapseExpand::class => \VuFindCollapseExpand\RecordTab\CollapseExpandFactory::class,
                ],
                'initializers' => [
                    \VuFindCollapseExpand\ServiceManager\ServiceInitializer::class
                ],
                'aliases' => [
                    'CollapseExpand' => \VuFindCollapseExpand\RecordTab\CollapseExpand::class,
                ],
            ],
            'search_backend' => [
                'factories' => [
                    'Solr' => \VuFindCollapseExpand\Search\Factory\SolrDefaultBackendFactory::class,
                ],
            ],
            'search_params'  => [
                'factories' => [
                    \VuFindCollapseExpand\Search\Solr\Params::class => \VuFindCollapseExpand\Search\Params\Factory::class . '::getSolr',
                    \VuFindCollapseExpand\Search\Solr\AuthorParams::class => \VuFindCollapseExpand\Search\Params\Factory::class . '::getSolrAuthor'
                ],
                'initializers' => [
                    \VuFindCollapseExpand\ServiceManager\ServiceInitializer::class
                ],
                'aliases' => [
                    \VuFind\Search\Solr\Params::class => \VuFindCollapseExpand\Search\Solr\Params::class,
                    \VuFind\Search\SolrAuthor\Params::class => \VuFindCollapseExpand\Search\Solr\AuthorParams::class,
                ]
            ]
        ],
        'template_injection' => [
            'VuFindCollapseExpand/'
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            '/usr/local/vufind/vendor/ubtue/vufind-collapse-expand/res/theme/templates',
        ],
    ],
];
$dir = __DIR__;
return $config;