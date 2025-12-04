<?php
namespace VuFindCollapseExpand\Module\Configuration;

$config = [
    'service_manager' => [
        'factories' => [
            'VuFindCollapseExpand\Config\Grouping'  => 'VuFindCollapseExpand\Config\Factory::getGrouping',
        ],
    ],
    'controllers' => [
        'factories' => [
            'VuFindCollapseExpand\Controller\AuthorController' => 'VuFind\Controller\AbstractBaseFactory',
            'VuFindCollapseExpand\Controller\SearchController' => 'VuFind\Controller\AbstractBaseFactory',
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
                    'VuFindCollapseExpand\AjaxHandler\GroupingCheckbox' =>
                        'VuFindCollapseExpand\AjaxHandler\GroupingCheckboxFactory',
                ],
                'aliases' => [
                    'groupingCheckbox' => 'VuFindCollapseExpand\AjaxHandler\GroupingCheckbox',
                ]
            ],
            'search_backend' => [
                'factories' => [
                    'Solr' => 'VuFindCollapseExpand\Search\Factory\SolrDefaultBackendFactory',
                ],
            ],
            'search_params'  => [
                'factories' => [
                    'VuFindCollapseExpand\Search\Solr\Params' => 'VuFindCollapseExpand\Search\Params\Factory::getSolr',
                    'VuFindCollapseExpand\Search\Solr\AuthorParams' => 'VuFindCollapseExpand\Search\Params\Factory::getSolrAuthor'
                ],
                'aliases' => [
                    'VuFind\Search\Solr\Params' => 'VuFindCollapseExpand\Search\Solr\Params',
                    'VuFind\Search\SolrAuthor\Params' => 'VuFindCollapseExpand\Search\Solr\AuthorParams',
                ]
            ],
        ],
        'template_injection' => [
            'VuFindCollapseExpand/'
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            '/usr/local/vufind/vendor/finc/vufind-collapse-expand/res/theme/templates',
        ],
    ],
];
$dir = __DIR__;
return $config;