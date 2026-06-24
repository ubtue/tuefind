<?php

namespace IxTheo\Module\Config;

$config = [
    'router' => [
        'routes' => [
            'classification' => [
                'type' => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/classification[/:notation]',
                    'constraints' => [
                        'notation' => '[a-zA-Z][a-zA-Z]*',
                    ],
                    'defaults' => [
                        'controller' => 'Classification',
                        'action'     => 'Home',
                    ],
                ],
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            'IxTheo\Controller\AlphabrowseController' => 'VuFind\Controller\AbstractBaseFactory',
            'IxTheo\Controller\BrowseController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
            'IxTheo\Controller\ClassificationController' => 'VuFind\Controller\AbstractBaseFactory',
            'IxTheo\Controller\MyResearchController' => 'VuFind\Controller\MyResearchControllerFactory',
            'IxTheo\Controller\RecordController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
            'IxTheo\Controller\Search\KeywordChainSearchController' => 'VuFind\Controller\AbstractBaseFactory',
        ],
        'aliases' => [
            'Alphabrowse' => 'IxTheo\Controller\AlphabrowseController',
            'alphabrowse' => 'IxTheo\Controller\AlphabrowseController',
            'Browse' => 'IxTheo\Controller\BrowseController',
            'browse' => 'IxTheo\Controller\BrowseController',
            'Classification' => 'IxTheo\Controller\ClassificationController',
            'classification' => 'IxTheo\Controller\ClassificationController',
            'KeywordChainSearch' => 'IxTheo\Controller\Search\KeywordChainSearchController',
            'Keywordchainsearch' => 'IxTheo\Controller\Search\KeywordChainSearchController',
            'MyResearch' => 'IxTheo\Controller\MyResearchController',
            'myresearch' => 'IxTheo\Controller\MyResearchController',
            'Record' => 'IxTheo\Controller\RecordController',
            'record' => 'IxTheo\Controller\RecordController',
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'IxTheo\Controller\Plugin\NewItems' => 'VuFind\Controller\Plugin\NewItemsFactory',
            'IxTheo\Controller\Plugin\Subscriptions' => 'Laminas\ServiceManager\Factory\InvokableFactory',
            'IxTheo\Controller\Plugin\PDASubscriptions' => 'IxTheo\Controller\Plugin\PDASubscriptionsFactory',
        ],
        'aliases' => [
            'newItems' => 'IxTheo\Controller\Plugin\NewItems',
            'pdasubscriptions' => 'IxTheo\Controller\Plugin\PDASubscriptions',
            'PDASubscriptions' => 'IxTheo\Controller\Plugin\PDASubscriptions',
            'subscriptions' => 'IxTheo\Controller\Plugin\Subscriptions',
            'Subscriptions' => 'IxTheo\Controller\Plugin\Subscriptions',

        ],
    ],
    'service_manager' => [
        'factories' => [

        ],
        'aliases' => [

        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'drivers' => [
                    'IxTheo\Db\Entity' => 'vufind_attribute_driver',
                ],
            ],
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'ajaxhandler' => [
                'factories' => [
                    'IxTheo\AjaxHandler\DeleteSubscription' => 'IxTheo\AjaxHandler\DeleteSubscriptionFactory',
                    'IxTheo\AjaxHandler\DeletePDASubscription' => 'IxTheo\AjaxHandler\DeletePDASubscriptionFactory',
                ],
                'aliases' => [
                    'deleteSubscription' => 'IxTheo\AjaxHandler\DeleteSubscription',
                    'deletePDASubscription' => 'IxTheo\AjaxHandler\DeletePDASubscription',
                ],
            ],
            'auth' => [
                'factories' => [
                    'IxTheo\Auth\Database' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'db' => 'IxTheo\Auth\Database',
                    'database' => 'IxTheo\Auth\Database',
                ],
            ],
            'autocomplete' => [
                'factories' => [
                    'IxTheo\Autocomplete\Solr' => 'IxTheo\Autocomplete\SolrFactory',
                ],
                'aliases' => [
                    'solr' => 'IxTheo\Autocomplete\Solr',
                ],
            ],
            'db_entity' => [
                'factories' => [
                    'IxTheo\Db\Entity\PDASubscription' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'IxTheo\Db\Entity\Subscription' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'IxTheo\Db\Entity\User' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'TueFind\Db\Entity\UserEntityInterface' => 'IxTheo\Db\Entity\UserEntityInterface',
                    'TueFind\Db\Entity\User' => 'IxTheo\Db\Entity\User',
                    'IxTheo\Db\Entity\PDASubscriptionEntityInterface' => 'IxTheo\Db\Entity\PDASubscription',
                    'IxTheo\Db\Entity\SubscriptionEntityInterface' => 'IxTheo\Db\Entity\Subscription',
                    'IxTheo\Db\Entity\UserEntityInterface' => 'IxTheo\Db\Entity\User',
                ],
            ],
            'db_service' => [
                'factories' => [
                    'IxTheo\Db\Service\PDASubscriptionService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'IxTheo\Db\Service\PublicationService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'IxTheo\Db\Service\SubscriptionService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'IxTheo\Db\Service\UserService' => 'VuFind\Db\Service\UserServiceFactory',
                    'IxTheo\Db\Service\UserAuthorityService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'IxTheo\Db\Service\UserAuthorityHistoryService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                ],
                'aliases' => [
                    'VuFind\Db\Service\UserServiceInterface' => 'IxTheo\Db\Service\UserService',
                    'TueFind\Db\Service\UserServiceInterface' => 'IxTheo\Db\Service\UserService',
                    'IxTheo\Db\Service\PDASubscriptionServiceInterface' => 'IxTheo\Db\Service\PDASubscriptionService',
                    'IxTheo\Db\Service\PublicationServiceInterface' => 'IxTheo\Db\Service\PublicationService',
                    'IxTheo\Db\Service\SubscriptionServiceInterface' => 'IxTheo\Db\Service\SubscriptionService',
                    'IxTheo\Db\Service\UserServiceInterface' => 'IxTheo\Db\Service\UserService',
                    'IxTheo\Db\Service\UserAuthorityServiceInterface' => 'IxTheo\Db\Service\UserAuthorityService',
                    'IxTheo\Db\Service\UserAuthorityHistoryServiceInterface' => 'IxTheo\Db\Service\UserAuthorityHistoryService',
                ],
            ],
            'navigation' => [
                'factories' => [
                    'IxTheo\Navigation\AccountMenu' => 'VuFind\Navigation\AccountMenuFactory',
                ],
                'aliases' => [
                    'VuFind\Navigation\AccountMenu' => 'IxTheo\Navigation\AccountMenu',
                ],
            ],
            'recommend' => [
                'factories' => [
                    'IxTheo\Recommend\BibleRanges' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'bibleranges' => 'IxTheo\Recommend\BibleRanges',
                ],
            ],
            'recorddataformatter_specs' => [
                'factories' => [
                    'IxTheo\RecordDataFormatter\Specs\DefaultRecord' => 'IxTheo\RecordDataFormatter\Specs\DefaultRecordFactory',
                ],
                'aliases' => [
                    'DefaultRecord' => 'IxTheo\RecordDataFormatter\Specs\DefaultRecord',
                    'VuFind\RecordDataFormatter\Specs\DefaultRecord' => 'IxTheo\RecordDataFormatter\Specs\DefaultRecord',
                ],
            ],
             'recorddriver' => [
                'factories' => [
                    'IxTheo\RecordDriver\SolrAuthDefault' => 'VuFind\RecordDriver\SolrDefaultWithoutSearchServiceFactory',
                    'IxTheo\RecordDriver\SolrAuthMarc' => 'VuFind\RecordDriver\SolrDefaultWithoutSearchServiceFactory',
                    'IxTheo\RecordDriver\SolrDefault' => 'TueFind\RecordDriver\SolrDefaultFactory',
                    'IxTheo\RecordDriver\SolrMarc' => 'TueFind\RecordDriver\SolrMarcFactory',
                ],
                'aliases' => [
                    'solrauth' => 'IxTheo\RecordDriver\SolrAuthMarc',
                    'solrauthdefault' => 'IxTheo\RecordDriver\SolrAuthMarc',
                    'solrauthmarc' => 'IxTheo\RecordDriver\SolrAuthMarc',
                    'solrdefault' => 'IxTheo\RecordDriver\SolrDefault',
                    'solrmarc' => 'IxTheo\RecordDriver\SolrMarc',
                ],
            ],
            'search_backend' => [
                'factories' => [
                    'Solr' => 'IxTheo\Search\Factory\SolrDefaultBackendFactory',
                    'Search2' => 'IxTheo\Search\Factory\Search2BackendFactory',
                ],
                'aliases' => [

                ],
            ],
            'search_options' => [
                'factories' => [
                    'IxTheo\Search\Search2\Options' => 'VuFind\Search\Options\OptionsFactory',
                    'IxTheo\Search\Solr\Options' => 'VuFind\Search\Options\OptionsFactory',
                    'IxTheo\Search\KeywordChainSearch\Options' => 'VuFind\Search\Options\OptionsFactory',
                    'IxTheo\Search\Subscriptions\Options' => 'VuFind\Search\Options\OptionsFactory',
                    'IxTheo\Search\PDASubscriptions\Options' => 'VuFind\Search\Options\OptionsFactory',
                ],
                'aliases' => [
                    'search2' => 'IxTheo\Search\Search2\Options',
                    'solr' => 'IxTheo\Search\Solr\Options',
                    'keywordchainsearch' => 'IxTheo\Search\KeywordChainSearch\Options',
                    'Subscriptions' => 'IxTheo\Search\Subscriptions\Options',
                    'pdasubscriptions' => 'IxTheo\Search\PDASubscriptions\Options',
                ],
            ],
            'search_params' => [
                'factories' => [

                ],
                'aliases' => [
                    'solr' => 'IxTheo\Search\Solr\Params',
                    'keywordchainsearch' => 'IxTheo\Search\KeywordChainSearch\Params',
                ],
            ],
            'search_results' => [
                'factories' => [
                    'IxTheo\Search\Search2\Results' => 'VuFind\Search\Search2\ResultsFactory',
                    'IxTheo\Search\Solr\Results' => 'VuFind\Search\Solr\ResultsFactory',
                    'IxTheo\Search\KeywordChainSearch\Results' => 'VuFind\Search\Solr\ResultsFactory',
                    'IxTheo\Search\Subscriptions\Results' => 'IxTheo\Search\Subscriptions\ResultsFactory',
                    'IxTheo\Search\PDASubscriptions\Results' => 'IxTheo\Search\PDASubscriptions\ResultsFactory',
                ],
                'aliases' => [
                    'search2' => 'IxTheo\Search\Search2\Results',
                    'solr' => 'IxTheo\Search\Solr\Results',
                    'keywordchainsearch' => 'IxTheo\Search\KeywordChainSearch\Results',
                    'Subscriptions' => 'IxTheo\Search\Subscriptions\Results',
                    'pdasubscriptions' => 'IxTheo\Search\PDASubscriptions\Results',
                ],
            ],
        ],
    ],
];

$nonTabRecordActions = ['PDASubscribe', 'Subscribe'];

$recordRoutes = [
    // needs to be registered again even if already registered in parent module,
    // for the nonTabRecordActions added in \IxTheo\Route\RouteGenerator
    'record' => 'Record',
    'search2record' => 'Search2Record',
    'search3record' => 'Search3Record',
];
$dynamicRoutes = [];
$staticRoutes = [
    'Browse/IxTheo-Classification',
    'Browse/Publisher',
    'Browse/RelBib-Classification',
    'Keywordchainsearch/Home',
    'Keywordchainsearch/Results',
    'Keywordchainsearch/Search',
    'MyResearch/Subscriptions',
    'MyResearch/DeleteSubscription',
    'MyResearch/PDASubscriptions',
    'MyResearch/DeletePDASubscription',
    'Classification/Home'
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addNonTabRecordActions($config, $nonTabRecordActions);
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
