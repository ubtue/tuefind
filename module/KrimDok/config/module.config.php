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
            'VuFind\Search\BackendManager' => 'KrimDok\Search\BackendManagerFactory',
            'KrimDok\Db\Row\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\Db\Table\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\RecordDriver\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\Search\Params\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'KrimDok\Search\Results\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory'
        ],
        'aliases' => [
            'VuFind\DbRowPluginManager' => 'KrimDok\Db\Row\PluginManager',
            'VuFind\Db\Row\PluginManager' => 'KrimDok\Db\Row\PluginManager',
            'VuFind\DbTablePluginManager' => 'KrimDok\Db\Table\PluginManager',
            'VuFind\Db\Table\PluginManager' => 'KrimDok\Db\Table\PluginManager',
            'VuFind\RecordDriverPluginManager' => 'KrimDok\RecordDriver\PluginManager',
            'VuFind\RecordDriver\PluginManager' => 'KrimDok\RecordDriver\PluginManager',
            'VuFind\SearchParamsPluginManager' => 'KrimDok\Search\Params\PluginManager',
            'VuFind\Search\Params\PluginManager' => 'KrimDok\Search\Params\PluginManager',
            'VuFind\Search\Results\PluginManager' => 'KrimDok\Search\Results\PluginManager'
        ],
    ],
    'vufind' => [
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrMarc' => [
                'tabs' => [
                    'Similar' => null,
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
