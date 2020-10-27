<?php
namespace TueFind\Module\Config;

$config = [
    'router' => [
        'routes' => [
            'proxy-load' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/Proxy/Load',
                    'defaults' => [
                        'controller' => 'Proxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'pdaproxy-load' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/PDAProxy/Load',
                    'defaults' => [
                        'controller' => 'PDAProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'fulltextsnippetproxy-load' => [
                'type' => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/FulltextSnippetProxy/Load',
                    'defaults' => [
                        'controller' => 'FulltextSnippetProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'wikidataproxy-load' => [
                'type'    => 'Zend\Router\Http\Literal',
                'options' => [
                    'route'    => '/WikidataProxy/Load',
                    'defaults' => [
                        'controller' => 'WikidataProxy',
                        'action'     => 'Load',
                    ],
                ],
            ],
            'quicklink' => [
                'type'    => 'Zend\Router\Http\Segment',
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
                'type'    => 'Zend\Router\Http\Segment',
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
            'static-page' => [
                'type'    => 'Zend\Router\Http\Segment',
                'options' => [
                    'route'    => "/:page",
                    'constraints' => [
                        'page'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'StaticPage',
                        'action'     => 'staticPage',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'TueFind\Controller\AjaxController' => 'VuFind\Controller\AjaxControllerFactory',
            'TueFind\Controller\AuthorityController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\CartController' => 'VuFind\Controller\CartControllerFactory',
            'TueFind\Controller\FeedbackController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\FulltextSnippetProxyController' => '\TueFind\Controller\FulltextSnippetProxyControllerFactory',
            'TueFind\Controller\MyResearchController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\PDAProxyController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\ProxyController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\QuickLinkController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\RecordController' => 'VuFind\Controller\AbstractBaseWithConfigFactory',
            'TueFind\Controller\RedirectController' => 'TueFind\Controller\RedirectControllerFactory',
            'TueFind\Controller\RssFeedController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\ShortlinkController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\StaticPageController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\WikidataProxyController' => 'VuFind\Controller\AbstractBaseFactory',
        ],
        'aliases' => [
            'AJAX' => 'TueFind\Controller\AjaxController',
            'ajax' => 'TueFind\Controller\AjaxController',
            'Authority' => 'TueFind\Controller\AuthorityController',
            'authority' => 'TueFind\Controller\AuthorityController',
            'Cart' => 'TueFind\Controller\CartController',
            'cart' => 'TueFind\Controller\CartController',
            'Feedback' => 'TueFind\Controller\FeedbackController',
            'feedback' => 'TueFind\Controller\FeedbackController',
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
            'Shortlink' => 'TueFind\Controller\ShortlinkController',
            'shortlink' => 'TueFind\Controller\ShortlinkController',
            'StaticPage' => 'TueFind\Controller\StaticPageController',
            'wikidataproxy' => 'TueFind\Controller\WikidataProxyController',
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'TueFind\Controller\Plugin\Recaptcha' => 'TueFind\Controller\Plugin\RecaptchaFactory',
            'TueFind\Controller\Plugin\Wikidata' => 'Zend\ServiceManager\Factory\InvokableFactory',
        ],
        'aliases' => [
            'recaptcha' => 'TueFind\Controller\Plugin\Recaptcha',
            'wikidata' => 'TueFind\Controller\Plugin\Wikidata',
        ],
    ],
    'service_manager' => [
        'allow_override' => true,
        'factories' => [
            'TueFind\Auth\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\ContentBlock\BlockLoader' => 'TueFind\ContentBlock\BlockLoaderFactory',
            'TueFind\ContentBlock\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Cookie\CookieManager' => 'VuFind\Cookie\CookieManagerFactory',
            'TueFind\Db\Row\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Db\Table\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Form\Form' => 'TueFind\Form\FormFactory',
            'TueFind\Mailer\Mailer' => 'TueFind\Mailer\Factory',
            'TueFind\MetadataVocabulary\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Service\ReCaptcha' => 'Zend\ServiceManager\Factory\InvokableFactory',
            'TueFind\Recommend\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Record\FallbackLoader\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Record\Loader' => 'VuFind\Record\LoaderFactory',
            'TueFind\RecordDriver\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\RecordTab\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Search\Results\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFindSearch\Service' => 'VuFind\Service\SearchServiceFactory',
            'Zend\Session\SessionManager' => 'TueFind\Session\ManagerFactory',
        ],
        'initializers' => [
            'TueFind\ServiceManager\ServiceInitializer',
        ],
        'aliases' => [
            'VuFind\AuthPluginManager' => 'TueFind\Auth\PluginManager',
            'VuFind\Auth\PluginManager' => 'TueFind\Auth\PluginManager',
            'VuFind\ContentBlock\BlockLoader' => 'TueFind\ContentBlock\BlockLoader',
            'VuFind\ContentBlock\PluginManager' => 'TueFind\ContentBlock\PluginManager',
            'VuFind\Cookie\CookieManager' => 'TueFind\Cookie\CookieManager',
            'VuFind\CookieManager' => 'TueFind\Cookie\CookieManager',
            'VuFind\DbRowPluginManager' => 'TueFind\Db\Row\PluginManager',
            'VuFind\Db\Row\PluginManager' => 'TueFind\Db\Row\PluginManager',
            'VuFind\DbTablePluginManager' => 'TueFind\Db\Table\PluginManager',
            'VuFind\Db\Table\PluginManager' => 'TueFind\Db\Table\PluginManager',
            'VuFind\Form\Form' => 'TueFind\Form\Form',
            'VuFind\Mailer\Mailer' => 'TueFind\Mailer\Mailer',
            'VuFind\Recaptcha' => 'TueFind\Service\ReCaptcha',
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
            'VuFind\Search\Results\PluginManager' => 'TueFind\Search\Results\PluginManager',
            'VuFindSearch\Service' => 'TueFindSearch\Service',
            'VuFind\Service\Recaptcha' => 'TueFind\Service\ReCaptcha',
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
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrAuthMarc' => [
                'tabs' => [
                    'ExternalAuthorityDatabases' => 'ExternalAuthorityDatabases',
                    'Details' => 'StaffViewMARC',
                ],
                'defaultTab' => null,
            ],
        ],
    ],
];

$recordRoutes = [];
$dynamicRoutes = [];
$staticRoutes = ['MyResearch/Newsletter', 'RssFeed/Full'];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
