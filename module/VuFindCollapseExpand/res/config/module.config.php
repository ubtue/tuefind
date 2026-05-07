<?php

namespace VuFindCollapseExpand\Module\Configuration;

$config = [
    'service_manager' => [
        'factories' => [
            'VuFindCollapseExpand\Config\CollapseExpand'  => 'VuFindCollapseExpand\Config\CollapseExpandFactory',
        ],
    ],
    'controllers' => [
        'factories' => [
            'VuFindCollapseExpand\Controller\AuthorController' => 'VuFind\Controller\AbstractBaseFactory',
            'VuFindCollapseExpand\Controller\SearchController' => 'VuFind\Controller\AbstractBaseFactory',
        ],
        'initializers' => [
            'VuFindCollapseExpand\ServiceManager\ServiceInitializer',
        ],
        'aliases' => [
            'VuFind\Controller\AuthorController'    => 'VuFindCollapseExpand\Controller\AuthorController',
            'VuFind\Controller\SearchController'    => 'VuFindCollapseExpand\Controller\SearchController',
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'ajaxhandler' => [
                'factories' => [
                    'VuFindCollapseExpand\AjaxHandler\CollapseExpandCheckbox' =>
                        'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'initializers' => [
                    'VuFindCollapseExpand\ServiceManager\ServiceInitializer',
                ],
                'aliases' => [
                    'collapseExpandCheckbox' => 'VuFindCollapseExpand\AjaxHandler\CollapseExpandCheckbox',
                ],
            ],
            'recorddriver' => [
                'initializers' => [
                    'VuFindCollapseExpand\ServiceManager\ServiceInitializer',
                ],
            ],
            'recordtab' => [
                'factories' => [
                    'VuFindCollapseExpand\RecordTab\CollapseExpand' => 'VuFindCollapseExpand\RecordTab\CollapseExpandFactory',
                ],
                'initializers' => [
                    'VuFindCollapseExpand\ServiceManager\ServiceInitializer',
                ],
                'aliases' => [
                    'CollapseExpand' => 'VuFindCollapseExpand\RecordTab\CollapseExpand',
                ],
            ],
            'search_backend' => [
                'factories' => [
                    'Solr' => 'VuFindCollapseExpand\Search\Factory\SolrDefaultBackendFactory',
                ],
            ],
            'search_params'  => [
                'factories' => [
                    'VuFindCollapseExpand\Search\Solr\Params' => 'VuFindCollapseExpand\Search\Params\Factory::getSolr',
                    'VuFindCollapseExpand\Search\Solr\AuthorParams' => 'VuFindCollapseExpand\Search\Params\Factory::getSolrAuthor',
                ],
                'initializers' => [
                    'VuFindCollapseExpand\ServiceManager\ServiceInitializer'
                ],
                'aliases' => [
                    'VuFind\Search\Solr\Params' => 'VuFindCollapseExpand\Search\Solr\Params',
                    'VuFind\Search\SolrAuthor\Params' => 'VuFindCollapseExpand\Search\Solr\AuthorParams',
                ],
            ],
        ],
    ],
];
$dir = __DIR__;
return $config;
