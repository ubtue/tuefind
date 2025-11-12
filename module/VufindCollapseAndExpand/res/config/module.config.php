<?php
namespace VufindCollapseAndExpand\Module\Configuration;

$config = [
    'service_manager' => [
        'factories' => [
            'VufindCollapseAndExpand\Config\Grouping'  => 'VufindCollapseAndExpand\Config\Factory::getGrouping',
        ],
    ],
    'controllers' => [
        'factories' => [
            'VufindCollapseAndExpand\Controller\AuthorController' => 'VuFind\Controller\AbstractBaseFactory',
            'VufindCollapseAndExpand\Controller\SearchController' => 'VuFind\Controller\AbstractBaseFactory',
        ],
        'aliases' => [
            'VuFind\Controller\AuthorController'    => 'VufindCollapseAndExpand\Controller\AuthorController',
            'VuFind\Controller\SearchController'    => 'VufindCollapseAndExpand\Controller\SearchController',
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'ajaxhandler' => [
                'factories' => [
                    'VufindCollapseAndExpand\AjaxHandler\GroupingCheckbox' =>
                        'VufindCollapseAndExpand\AjaxHandler\GroupingCheckboxFactory',
                ],
                'aliases' => [
                    'groupingCheckbox' => 'VufindCollapseAndExpand\AjaxHandler\GroupingCheckbox',
                ]
            ],
            'search_backend' => [
                'factories' => [
                    'Solr' => 'VufindCollapseAndExpand\Search\Factory\SolrDefaultBackendFactory',
                ],
            ],
            'search_params'  => [
                'factories' => [
                    'VufindCollapseAndExpand\Search\Solr\Params' => 'VufindCollapseAndExpand\Search\Params\Factory::getSolr',
                    'VufindCollapseAndExpand\Search\Solr\AuthorParams' => 'VufindCollapseAndExpand\Search\Params\Factory::getSolrAuthor'
                ],
                'aliases' => [
                    'VuFind\Search\Solr\Params' => 'VufindCollapseAndExpand\Search\Solr\Params',
                    'VuFind\Search\SolrAuthor\Params' => 'VufindCollapseAndExpand\Search\Solr\AuthorParams',
                ]
            ],
        ],
        'template_injection' => [
            'VufindCollapseAndExpand/'
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            '/usr/local/vufind/vendor/finc/vufind-results-grouping/res/theme/templates',
        ],
    ],
];
$dir = __DIR__;
return $config;