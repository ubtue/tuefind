<?php
namespace TueFind\Module\Config;

$config = [
    'router' => [
        'routes' => [
            'content-page' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/Content/:page[/:container]',
                    'constraints' => [
                        'page'      => '[a-zA-Z][a-zA-Z0-9_-]*',
                        // Override: Add information about whether we want to
                        // include the html template in a container
                        // (default true)
                        'container' => '(true|false)',
                    ],
                    'defaults' => [
                        'controller' => 'Content',
                        'action'     => 'Content',
                    ]
                ],
            ],
            'proxy-load' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/Proxy/Load',
                    'defaults' => [
                        'controller' => 'Proxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'pdaproxy-load' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/PDAProxy/Load',
                    'defaults' => [
                        'controller' => 'PDAProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'findbuchproxy-load' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/FindbuchProxy/Load',
                    'defaults' => [
                        'controller' => 'FindbuchProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'fulltextsnippetproxy-load' => [
                'type' => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/FulltextSnippetProxy/Load',
                    'defaults' => [
                        'controller' => 'FulltextSnippetProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'wikidataproxy-load' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/WikidataProxy/Load',
                    'defaults' => [
                        'controller' => 'WikidataProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'zederproxy-load' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/Zeder/Proxy[/:targetId]',
                    'constraints' => [
                        'targetId'     => '[^/]+',
                    ],
                    'defaults' => [
                        'controller' => 'ZederProxy',
                        'action'     => 'Proxy',
                    ],
                ],
            ],
            'zederproxy-view' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/Zeder/View[/:viewId]',
                    'constraints' => [
                        'viewId'     => '[^/]+',
                    ],
                    'defaults' => [
                        'controller' => 'ZederProxy',
                        'action'     => 'View',
                    ],
                ],
            ],
            'quicklink' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/r/[:id]',
                    'constraints' => [
                        'id'     => '[a-zA-Z0-9._-]+',
                    ],
                    'defaults' => [
                        'controller' => 'QuickLink',
                        'action'     => 'redirect',
                    ]
                ],
            ],
            'redirect' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/redirect/:url[/:group]',
                    'constraints' => [
                        // URL needs to be base64, see controller for details
                        'url'     => '[^/]+',
                        'group'   => '[^/]+',
                    ],
                    'defaults' => [
                        'controller' => 'Redirect',
                        'action'     => 'redirect',
                    ]
                ],
            ],
            'redirect-license' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/redirect-license/:id[/:proxy-url]',
                    'constraints' => [
                        // The ID can either be a regular PPN, or a HAN-ID.
                        'id'        => '[^/]+',
                        // The Proxy-URL is sent by the HAN Server
                        // if there was a timeout on an existing session
                        // and a user needs to re-authenticate before
                        // being able to use the resource again.
                        // The URL will contain detailed information about
                        // e.g. the last viewed page in the document.
                        'proxy-url' => '.+',
                    ],
                    'defaults' => [
                        'controller' => 'Redirect',
                        'action'     => 'license',
                    ],
                ],
            ],
            'myresearch-publish' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => "/MyResearch/Publish/[:record_id]",
                    'constraints' => [
                        'record_id'     => '[0-9A-Z]{8,}',
                    ],
                    'defaults' => [
                        'controller' => 'MyResearch',
                        'action'     => 'publish',
                    ],
                ],
            ],
            'myresearch-rssfeedraw' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => "/myrssfeed/:user_uuid",
                    'constraints' => [
                        //example: 134b5a64-97ab-11eb-baff-309c23c4daa6
                        'user_uuid'     => '.{8}(-.{4}){3}-.{12}',
                    ],
                    'defaults' => [
                        'controller' => 'MyResearch',
                        'action'     => 'rssFeedRaw',
                    ],
                ],
            ],
            'authority-request-access' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => "/Authority/RequestAccess/:authority_id",
                    'constraints' => [
                        'authority_id'     => '[0-9A-Z]{8,}',
                    ],
                    'defaults' => [
                        'controller' => 'Authority',
                        'action'     => 'requestAccess',
                    ],
                ],
            ],
            'authority-process-request' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => "/Authority/RequestAccess/:authority_id/:user_id",
                    'constraints' => [
                        'authority_id'     => '[0-9A-Z]{8,}',
                        'user_id'          => '\d+',
                    ],
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'ProcessUserAuthorityRequest',
                    ],
                ],
            ],
            'crawler-info' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => "/crawler",
                    'defaults' => [
                        'controller' => 'Content',
                        'action'     => 'Content',
                        'page'       => 'crawler'

                    ]
                ],
            ],
            'last_updated-info' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => "/Last_Updated",
                    'defaults' => [
                        'controller' => 'Content',
                        'action'     => 'Content',
                        'page'       => 'Last_Updated'

                    ]
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'TueFind\Controller\AdminFrontendController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\AjaxController' => 'VuFind\Controller\AjaxControllerFactory',
            'TueFind\Controller\AuthorController' => '\VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\AuthorityController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\CartController' => 'VuFind\Controller\CartControllerFactory',
            'TueFind\Controller\FeedbackController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\FindbuchProxyController' => 'TueFind\Controller\AbstractProxyControllerFactory',
            'TueFind\Controller\FulltextSnippetProxyController' => '\TueFind\Controller\FulltextSnippetProxyControllerFactory',
            'TueFind\Controller\MyResearchController' => 'VuFind\Controller\MyResearchControllerFactory',
            'TueFind\Controller\PDAProxyController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\ProxyController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\QuickLinkController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\RecordController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
            'TueFind\Controller\RedirectController' => 'TueFind\Controller\RedirectControllerFactory',
            'TueFind\Controller\RssFeedController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\Search2recordController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\Search3recordController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\Search3Controller' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\WikidataProxyController' => 'TueFind\Controller\AbstractProxyControllerFactory',
            'TueFind\Controller\ZederProxyController' => 'TueFind\Controller\AbstractProxyControllerFactory',
        ],
        'initializers' => [
            'TueFind\ServiceManager\ServiceInitializer',
        ],
        'aliases' => [
            'AdminFrontend' => 'TueFind\Controller\AdminFrontendController',
            'AJAX' => 'TueFind\Controller\AjaxController',
            'ajax' => 'TueFind\Controller\AjaxController',
            'Author' => 'TueFind\Controller\AuthorController',
            'author' => 'TueFind\Controller\AuthorController',
            'Authority' => 'TueFind\Controller\AuthorityController',
            'authority' => 'TueFind\Controller\AuthorityController',
            'Cart' => 'TueFind\Controller\CartController',
            'cart' => 'TueFind\Controller\CartController',
            'Feedback' => 'TueFind\Controller\FeedbackController',
            'feedback' => 'TueFind\Controller\FeedbackController',
            'FindbuchProxy' => 'TueFind\Controller\FindbuchProxyController',
            'findbuchproxy' => 'TueFind\Controller\FindbuchProxyController',
            'fulltextsnippetproxy' => 'TueFind\Controller\FulltextSnippetProxyController',
            'MyResearch' => 'TueFind\Controller\MyResearchController',
            'myresearch' => 'TueFind\Controller\MyResearchController',
            'pdaproxy' => 'TueFind\Controller\PDAProxyController',
            'proxy' => 'TueFind\Controller\ProxyController',
            'QuickLink' => 'TueFind\Controller\QuickLinkController',
            'Record' => 'TueFind\Controller\RecordController',
            'record' => 'TueFind\Controller\RecordController',
            'Redirect' => 'TueFind\Controller\RedirectController',
            'redirect' => 'TueFind\Controller\RedirectController',
            'RssFeed' => 'TueFind\Controller\RssFeedController',
            'rssfeed' => 'TueFind\Controller\RssFeedController',
            'search2record' => 'TueFind\Controller\Search2recordController',
            'Search2Record' => 'TueFind\Controller\Search2recordController',
            'search3record' => 'TueFind\Controller\Search3recordController',
            'Search3Record' => 'TueFind\Controller\Search3recordController',
            'Search3' => 'TueFind\Controller\Search3Controller',
            'search3' => 'TueFind\Controller\Search3Controller',
            'WikidataProxy' => 'TueFind\Controller\WikidataProxyController',
            'wikidataproxy' => 'TueFind\Controller\WikidataProxyController',
            'ZederProxy' => 'TueFind\Controller\ZederProxyController',
            'zederproxy' => 'TueFind\Controller\ZederProxyController',
        ],
    ],
    'controller_plugins' => [

    ],
    'service_manager' => [
        'allow_override' => true,
        'factories' => [
            'TueFind\AjaxHandler\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Export' => 'VuFind\ExportFactory',
            'TueFind\Auth\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Cache\Manager' => 'VuFind\Cache\ManagerFactory',
            'TueFind\Captcha\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Config\AccountCapabilities' => 'TueFind\Config\AccountCapabilitiesFactory',
            'TueFind\Config\Handler\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\ContentBlock\BlockLoader' => 'TueFind\ContentBlock\BlockLoaderFactory',
            'TueFind\ContentBlock\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Cookie\CookieManager' => 'VuFind\Cookie\CookieManagerFactory',
            'TueFind\Db\Entity\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Db\Service\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Form\Form' => 'TueFind\Form\FormFactory',
            'TueFind\Form\Handler\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Sitemap\Generator' => 'VuFind\Sitemap\GeneratorFactory',
            'TueFind\Mailer\Mailer' => 'TueFind\Mailer\Factory',
            'TueFind\MetadataVocabulary\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Navigation\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Recommend\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Record\FallbackLoader\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Record\Loader' => 'VuFind\Record\LoaderFactory',
            'TueFind\RecordDriver\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\RecordTab\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\RecordTab\ItemFulltextSearch' => 'Laminas\ServiceManager\Factory\InvokableFactory',
            'TueFind\Search\Options\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Search\Params\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Search\Results\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Service\DSpace6' => 'TueFind\Service\DSpaceFactory',
            'TueFind\Service\DSpace7' => 'TueFind\Service\DSpaceFactory',
            'TueFind\Service\KfL' => 'TueFind\Service\KfLFactory',
            'TueFind\Sitemap\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFindSearch\Service' => 'VuFind\Service\SearchServiceFactory',
            'Laminas\Session\SessionManager' => 'TueFind\Session\ManagerFactory',
        ],
        'initializers' => [
            'TueFind\ServiceManager\ServiceInitializer',
        ],
        'aliases' => [
            'VuFind\AccountCapabilities' => 'TueFind\Config\AccountCapabilities',
            'VuFind\AjaxHandler\PluginManager' => 'TueFind\AjaxHandler\PluginManager',
            'VuFind\AuthPluginManager' => 'TueFind\Auth\PluginManager',
            'VuFind\Auth\PluginManager' => 'TueFind\Auth\PluginManager',
            'VuFind\Cover\CachingProxy' => 'TueFind\Cover\CachingProxy',
            'VuFind\Cache\Manager' => 'TueFind\Cache\Manager',
            'VuFind\Captcha\PluginManager' => 'TueFind\Captcha\PluginManager',
            'VuFind\Config\AccountCapabilities' => 'TueFind\Config\AccountCapabilities',
            'VuFind\Config\Handler\PluginManager' => 'TueFind\Config\Handler\PluginManager',
            'VuFind\ContentBlock\BlockLoader' => 'TueFind\ContentBlock\BlockLoader',
            'VuFind\ContentBlock\PluginManager' => 'TueFind\ContentBlock\PluginManager',
            'VuFind\Cookie\CookieManager' => 'TueFind\Cookie\CookieManager',
            'VuFind\CookieManager' => 'TueFind\Cookie\CookieManager',
            'VuFind\Db\Entity\PluginManager' => 'TueFind\Db\Entity\PluginManager',
            'VuFind\Db\Service\PluginManager' => 'TueFind\Db\Service\PluginManager',
            'VuFind\Export' => 'TueFind\Export',
            'VuFind\Form\Form' => 'TueFind\Form\Form',
            'VuFind\Form\Handler\PluginManager' => 'TueFind\Form\Handler\PluginManager',
            'VuFind\Mailer\Mailer' => 'TueFind\Mailer\Mailer',
            'VuFind\MetadataVocabulary\PluginManager' => 'TueFind\MetadataVocabulary\PluginManager',
            'VuFind\Navigation\PluginManager' => 'TueFind\Navigation\PluginManager',
            'VuFind\RecommendPluginManager' => 'TueFind\Recommend\PluginManager',
            'VuFind\Recommend\PluginManager' => 'TueFind\Recommend\PluginManager',
            'VuFind\Record\FallbackLoader\PluginManager' => 'TueFind\Record\FallbackLoader\PluginManager',
            'VuFind\Record\Loader' => 'TueFind\Record\Loader',
            'VuFind\RecordLoader' => 'TueFind\Record\Loader',
            'VuFind\RecordDriverPluginManager' => 'TueFind\RecordDriver\PluginManager',
            'VuFind\RecordDriver\PluginManager' => 'TueFind\RecordDriver\PluginManager',
            'VuFind\RecordTabPluginManager' => 'TueFind\RecordTab\PluginManager',
            'VuFind\RecordTab\PluginManager' => 'TueFind\RecordTab\PluginManager',
            'VuFind\Search' => 'TueFindSearch\Service',
            'VuFind\Search\Options\PluginManager' => 'TueFind\Search\Options\PluginManager',
            'VuFind\Search\Params\PluginManager' => 'TueFind\Search\Params\PluginManager',
            'VuFind\Search\Results\PluginManager' => 'TueFind\Search\Results\PluginManager',
            'VuFind\Sitemap\Generator' => 'TueFind\Sitemap\Generator',
            'VuFind\Sitemap\PluginManager' => 'TueFind\Sitemap\PluginManager',
            'VuFindSearch\Service' => 'TueFindSearch\Service',
        ],
    ],
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'drivers' => [
                    'TueFind\Db\Entity' => 'vufind_attribute_driver',
                ],
            ],
        ],
    ],
    'view_helpers' => [
        'initializers' => [
            'TueFind\ServiceManager\ServiceInitializer',
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'metadatavocabulary' => [],
        ],
    ],
];

$recordRoutes = [];
$nonTabRecordActions = ['Publish'];
$dynamicRoutes = [];
$staticRoutes = [
    'AdminFrontend/ShowAdmins',
    'AdminFrontend/ShowUserAuthorities',
    'AdminFrontend/ShowUserAuthorityHistory',
    'AdminFrontend/ShowUserPublications',
    'AdminFrontend/ShowUserPublicationStatistics',
    'MyResearch/Newsletter',
    'MyResearch/Publications',
    'MyResearch/RssFeedSettings',
    'MyResearch/RssFeedPreview',
    'MyResearch/SelfArchiving',
    'RssFeed/Full',
    'Search3/Home',
    'Search3/Results',
    'Search3/FacetList',
    'Search3/Versions',
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addNonTabRecordActions($config, $nonTabRecordActions);
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
