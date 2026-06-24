<?php

namespace KrimDok\Module\Config;

$config = [
    'controllers' => [
        'factories' => [
            'KrimDok\Controller\BrowseController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
            'KrimDok\Controller\HelpController' => 'VuFind\Controller\AbstractBaseFactory',
            'KrimDok\Controller\MyResearchController' => 'VuFind\Controller\MyResearchControllerFactory',
        ],
        'aliases' => [
            'Browse' => 'KrimDok\Controller\BrowseController',
            'browse' => 'KrimDok\Controller\BrowseController',
            'Help' => 'KrimDok\Controller\HelpController',
            'help' => 'KrimDok\Controller\HelpController',
            'MyResearch' => 'KrimDok\Controller\MyResearchController',
            'myresearch' => 'KrimDok\Controller\MyResearchController',
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'KrimDok\Controller\Plugin\NewItems' => 'VuFind\Controller\Plugin\NewItemsFactory',
        ],
        'aliases' => [
            'newItems' => 'KrimDok\Controller\Plugin\NewItems',
        ],
    ],
    'service_manager' => [
        'factories' => [
            'KrimDok\Db\Entity\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\Db\Service\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\RecordDriver\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\Search\Params\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\Search\Results\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory'
        ],
        'aliases' => [
            'VuFind\Db\Entity\PluginManager' => 'KrimDok\Db\Entity\PluginManager',
            'VuFind\Db\Service\PluginManager' => 'KrimDok\Db\Service\PluginManager',
            'VuFind\RecordDriverPluginManager' => 'KrimDok\RecordDriver\PluginManager',
            'VuFind\RecordDriver\PluginManager' => 'KrimDok\RecordDriver\PluginManager',
            'VuFind\SearchParamsPluginManager' => 'KrimDok\Search\Params\PluginManager',
            'VuFind\Search\Params\PluginManager' => 'KrimDok\Search\Params\PluginManager',
            'VuFind\Search\Results\PluginManager' => 'KrimDok\Search\Results\PluginManager'
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'drivers' => [
                    'KrimDok\Db\Entity' => 'vufind_attribute_driver',
                ],
            ],
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'auth' => [
                'factories' => [
                    'KrimDok\Auth\Database' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'db' => 'KrimDok\Auth\Database',
                    'database' => 'KrimDok\Auth\Database',
                ],
            ],
            'recorddataformatter_specs' => [
                'factories' => [
                    'KrimDok\RecordDataFormatter\Specs\DefaultRecord' => 'VuFind\RecordDataFormatter\Specs\DefaultRecordFactory',
                ],
                'aliases' => [
                    'DefaultRecord' => 'KrimDok\RecordDataFormatter\Specs\DefaultRecord',
                    'VuFind\RecordDataFormatter\Specs\DefaultRecord' => 'KrimDok\RecordDataFormatter\Specs\DefaultRecord',
                ],
            ],
            'search_backend' => [
                'factories' => [
                    'Solr' => 'KrimDok\Search\Factory\SolrDefaultBackendFactory',
                    'Search2' => 'KrimDok\Search\Factory\Search2BackendFactory',
                ],
                'aliases' => [

                ],
            ],
        ],
    ],
];

$recordRoutes = [ 'search2record' => 'Search2Record' ];
$dynamicRoutes = [];
$staticRoutes = [
    'Help/FAQ',
    'MyResearch/Newsletter',
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
