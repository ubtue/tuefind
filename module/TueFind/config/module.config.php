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
                    ],
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
                    ],
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
                    ],
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
                    'route'    => '/MyResearch/Publish/[:record_id]',
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
                    'route'    => '/myrssfeed/:user_uuid',
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
                    'route'    => '/Authority/RequestAccess/:authority_id',
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
                    'route'    => '/Authority/RequestAccess/:authority_id/:user_id',
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
                    'route'    => '/crawler',
                    'defaults' => [
                        'controller' => 'Content',
                        'action'     => 'Content',
                        'page'       => 'crawler',

                    ],
                ],
            ],
            'last_updated-info' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/Last_Updated',
                    'defaults' => [
                        'controller' => 'Content',
                        'action'     => 'Content',
                        'page'       => 'Last_Updated',

                    ],
                ],
            ],
            'adminfrontend-cmspages' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/AdminFrontend/CMSPages',
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'CMSPages',
                    ],
                ],
            ],
            'adminfrontend-updatecmspage' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/AdminFrontend/updateCMSPage/:cms_page_id',
                    'constraints' => [
                        'cms_page_id'     => '\d+',
                    ],
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'updateCMSPage',
                    ],
                ],
            ],
            'adminfrontend-deletecmspage' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/AdminFrontend/deleteCMSPage/:cms_page_id',
                    'constraints' => [
                        'cms_page_id'     => '\d+',
                    ],
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'deleteCMSPage',
                    ],
                ],
            ],
            'adminfrontend-cmspageshistory' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/AdminFrontend/CMSPagesHistory/:cms_page_id',
                    'constraints' => [
                        'cms_page_id'     => '\d+',
                    ],
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'CMSPagesHistory',
                    ],
                ],
            ],
            'adminfrontend-cmspagesallhistory' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/AdminFrontend/CMSPagesAllHistory',
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'CMSPagesAllHistory',
                    ],
                ],
            ],
            'adminfrontend-addcmspage' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/AdminFrontend/addCMSPage',
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'addCMSPage',
                    ],
                ],
            ],
            'adminfrontend-cmspagesdocs' => [
                'type'    => 'Laminas\Router\Http\Segment',
                'options' => [
                    'route'    => '/AdminFrontend/CMSPagesDocs',
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'CMSPagesDocs',
                    ],
                ],
            ],
            'adminfrontend-cmspagesfiles' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/AdminFrontend/CMSPagesFiles',
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'CMSPagesFiles',
                    ],
                ],
            ],
            'adminfrontend-cmspagesimages' => [
                'type'    => 'Laminas\Router\Http\Literal',
                'options' => [
                    'route'    => '/AdminFrontend/CMSPagesImages',
                    'defaults' => [
                        'controller' => 'AdminFrontend',
                        'action'     => 'CMSPagesImages',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'TueFind\Controller\AdminFrontendController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\AuthorController' => '\VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\AuthorityController' => 'VuFind\Controller\AbstractBaseFactory',
            'TueFind\Controller\CartController' => 'VuFind\Controller\CartControllerFactory',
            'TueFind\Controller\ContentController' => 'VuFind\Controller\AbstractBaseFactory',
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
            'Author' => 'TueFind\Controller\AuthorController',
            'author' => 'TueFind\Controller\AuthorController',
            'Authority' => 'TueFind\Controller\AuthorityController',
            'authority' => 'TueFind\Controller\AuthorityController',
            'Cart' => 'TueFind\Controller\CartController',
            'cart' => 'TueFind\Controller\CartController',
            'Content' => 'TueFind\Controller\ContentController',
            'content' => 'TueFind\Controller\ContentController',
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
            'TueFind\Cache\Manager' => 'VuFind\Cache\ManagerFactory',
            'TueFind\Config\AccountCapabilities' => 'TueFind\Config\AccountCapabilitiesFactory',
            'TueFind\ContentBlock\BlockLoader' => 'TueFind\ContentBlock\BlockLoaderFactory',
            'TueFind\Cookie\CookieManager' => 'VuFind\Cookie\CookieManagerFactory',
            'TueFind\Export' => 'VuFind\ExportFactory',
            'TueFind\Form\Form' => 'TueFind\Form\FormFactory',
            'TueFind\Form\Handler\PluginManager' => 'VuFind\ServiceManager\AbstractPluginManagerFactory',
            'TueFind\Sitemap\Generator' => 'VuFind\Sitemap\GeneratorFactory',
            'TueFind\Mailer\Mailer' => 'TueFind\Mailer\Factory',
            'TueFind\Record\Loader' => 'VuFind\Record\LoaderFactory',
            'TueFind\RecordTab\ItemFulltextSearch' => 'Laminas\ServiceManager\Factory\InvokableFactory',
            'TueFind\Service\CmsSync' => 'TueFind\Service\CmsSyncFactory',
            'TueFind\Service\DSpace6' => 'TueFind\Service\DSpaceFactory',
            'TueFind\Service\DSpace7' => 'TueFind\Service\DSpaceFactory',
            'TueFind\Service\KfL' => 'TueFind\Service\KfLFactory',
            'TueFindSearch\Service' => 'VuFind\Service\SearchServiceFactory',
            'Laminas\Session\SessionManager' => 'TueFind\Session\ManagerFactory',
        ],
        'initializers' => [
            'TueFind\ServiceManager\ServiceInitializer',
        ],
        'aliases' => [
            'VuFind\AccountCapabilities' => 'TueFind\Config\AccountCapabilities',
            'VuFind\Cover\CachingProxy' => 'TueFind\Cover\CachingProxy',
            'VuFind\Cache\Manager' => 'TueFind\Cache\Manager',
            'VuFind\Config\AccountCapabilities' => 'TueFind\Config\AccountCapabilities',
            'VuFind\ContentBlock\BlockLoader' => 'TueFind\ContentBlock\BlockLoader',
            'VuFind\Cookie\CookieManager' => 'TueFind\Cookie\CookieManager',
            'VuFind\CookieManager' => 'TueFind\Cookie\CookieManager',
            'VuFind\Export' => 'TueFind\Export',
            'VuFind\Form\Form' => 'TueFind\Form\Form',
            'VuFind\Form\Handler\PluginManager' => 'TueFind\Form\Handler\PluginManager',
            'VuFind\Mailer\Mailer' => 'TueFind\Mailer\Mailer',
            'VuFind\Record\Loader' => 'TueFind\Record\Loader',
            'VuFind\RecordLoader' => 'TueFind\Record\Loader',
            'VuFind\Search' => 'TueFindSearch\Service',
            'VuFind\Sitemap\Generator' => 'TueFind\Sitemap\Generator',
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
            'ajaxhandler' => [
                'factories' => [
                    'TueFind\AjaxHandler\GetSubscriptionBundleEntries' => 'TueFind\AjaxHandler\GetSubscriptionBundleEntriesFactory',
                    'TueFind\AjaxHandler\CmsDocsEntries' => 'TueFind\AjaxHandler\CmsDocsEntriesFactory',
                    'TueFind\AjaxHandler\MappingEntries' => 'TueFind\AjaxHandler\MappingEntriesFactory',
                ],
                'aliases' => [
                    'getSubscriptionBundleEntries' => 'TueFind\AjaxHandler\GetSubscriptionBundleEntries',
                    'CmsDocs' => 'TueFind\AjaxHandler\CmsDocsEntries',
                    'Mapping' => 'TueFind\AjaxHandler\MappingEntries',
                ],
            ],
            'auth' => [
                'factories' => [
                    'TueFind\Auth\Database' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'db' => 'TueFind\Auth\Database',
                    'database' => 'TueFind\Auth\Database',
                ],
            ],
            'captcha' => [
                'factories' => [
                    'VuFind\Captcha\Image' => 'TueFind\Captcha\ImageFactory',
                ],
                'aliases' => [

                ],
            ],
            'contentblock' => [
                'factories' => [
                    'TueFind\ContentBlock\Home' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'home' => 'TueFind\ContentBlock\Home',
                ],
            ],
            'db_entity' => [
                'factories' => [
                    'TueFind\Db\Entity\CmsPages' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\CmsPagesHistory' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\CmsPagesTranslation' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\Publication' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\Redirect' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\RssFeed' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\RssItem' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\RssSubscription' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\User' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\UserAuthority' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\UserAuthorityHistory' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Db\Entity\Subsystems' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'VuFind\Db\Entity\UserEntityInterface' => 'TueFind\Db\Entity\UserEntityInterface',
                    'TueFind\Db\Entity\CmsPagesEntityInterface' => 'TueFind\Db\Entity\CmsPages',
                    'TueFind\Db\Entity\CmsPagesTranslationEntityInterface' => 'TueFind\Db\Entity\CmsPagesTranslation',
                    'TueFind\Db\Entity\CmsPagesHistoryEntityInterface' => 'TueFind\Db\Entity\CmsPagesHistory',
                    'TueFind\Db\Entity\PublicationEntityInterface' => 'TueFind\Db\Entity\Publication',
                    'TueFind\Db\Entity\RedirectEntityInterface' => 'TueFind\Db\Entity\Redirect',
                    'TueFind\Db\Entity\RssFeedEntityInterface' => 'TueFind\Db\Entity\RssFeed',
                    'TueFind\Db\Entity\RssItemEntityInterface' => 'TueFind\Db\Entity\RssItem',
                    'TueFind\Db\Entity\RssSubscriptionEntityInterface' => 'TueFind\Db\Entity\RssSubscription',
                    'TueFind\Db\Entity\SubsystemsEntityInterface' => 'TueFind\Db\Entity\Subsystems',
                    'TueFind\Db\Entity\UserEntityInterface' => 'TueFind\Db\Entity\User',
                    'TueFind\Db\Entity\UserAuthorityEntityInterface' => 'TueFind\Db\Entity\UserAuthority',
                    'TueFind\Db\Entity\UserAuthorityHistoryEntityInterface' => 'TueFind\Db\Entity\UserAuthorityHistory',
                ],
            ],
            'db_service' => [
                'factories' => [
                    'TueFind\Db\Service\CmsPagesService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\CmsPagesTranslationService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\CmsPagesHistoryService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\PublicationService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\RedirectService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\RssFeedService' => 'TueFind\Db\Service\RssFactory',
                    'TueFind\Db\Service\RssItemService' => 'TueFind\Db\Service\RssFactory',
                    'TueFind\Db\Service\RssSubscriptionService' => 'TueFind\Db\Service\RssFactory',
                    'TueFind\Db\Service\SubsystemsService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\UserService' => 'VuFind\Db\Service\UserServiceFactory',
                    'TueFind\Db\Service\UserAuthorityService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                    'TueFind\Db\Service\UserAuthorityHistoryService' => 'VuFind\Db\Service\AbstractDbServiceFactory',
                ],
                'aliases' => [
                    'VuFind\Db\Service\UserServiceInterface' => 'TueFind\Db\Service\UserServiceInterface',
                    'TueFind\Db\Service\CmsPagesServiceInterface' => 'TueFind\Db\Service\CmsPagesService',
                    'TueFind\Db\Service\CmsPagesTranslationServiceInterface' => 'TueFind\Db\Service\CmsPagesTranslationService',
                    'TueFind\Db\Service\CmsPagesHistoryServiceInterface' => 'TueFind\Db\Service\CmsPagesHistoryService',
                    'TueFind\Db\Service\PublicationServiceInterface' => 'TueFind\Db\Service\PublicationService',
                    'TueFind\Db\Service\RedirectServiceInterface' => 'TueFind\Db\Service\RedirectService',
                    'TueFind\Db\Service\RssFeedServiceInterface' => 'TueFind\Db\Service\RssFeedService',
                    'TueFind\Db\Service\RssItemServiceInterface' => 'TueFind\Db\Service\RssItemService',
                    'TueFind\Db\Service\RssSubscriptionServiceInterface' => 'TueFind\Db\Service\RssSubscriptionService',
                    'TueFind\Db\Service\SubsystemsServiceInterface' => 'TueFind\Db\Service\SubsystemsService',
                    'TueFind\Db\Service\UserServiceInterface' => 'TueFind\Db\Service\UserService',
                    'TueFind\Db\Service\UserAuthorityServiceInterface' => 'TueFind\Db\Service\UserAuthorityService',
                    'TueFind\Db\Service\UserAuthorityHistoryServiceInterface' => 'TueFind\Db\Service\UserAuthorityHistoryService',
                ],
            ],
            'form_handler' => [
                'factories' => [
                    'TueFind\Form\Handler\Email' => 'VuFind\Form\Handler\EmailFactory',
                ],
                'aliases' => [
                    'email' => 'TueFind\Form\Handler\Email',
                ],
            ],
            'metadatavocabulary' => [
                'factories' => [
                    'TueFind\MetadataVocabulary\HighwirePress' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'HighwirePress' => 'TueFind\MetadataVocabulary\HighwirePress',
                    'VuFind\MetadataVocabulary\HighwirePress' => 'TueFind\MetadataVocabulary\HighwirePress',
                ],
            ],
            'navigation' => [
                'factories' => [
                    'TueFind\Navigation\AccountMenu' => 'VuFind\Navigation\AccountMenuFactory',
                ],
                'aliases' => [
                    'VuFind\Navigation\AccountMenu' => 'TueFind\Navigation\AccountMenu',
                ],
            ],
            'recommend' => [
                'factories' => [
                    'TueFind\Recommend\Ids' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\Recommend\SideFacets' => 'VuFind\Recommend\SideFacetsFactory',
                ],
                'aliases' => [
                    'ids' => 'TueFind\Recommend\Ids',
                    'sidefacets' => 'TueFind\Recommend\SideFacets',
                ],
            ],
            'record_fallbackloader' => [
                'factories' => [
                    'TueFind\Record\FallbackLoader\Solr' => 'TueFind\Record\FallbackLoader\SolrFactory',
                ],
                'aliases' => [
                    'solr' => 'TueFind\Record\FallbackLoader\Solr',
                ],
            ],
            'recorddataformatter_specs' => [
                'factories' => [
                    'TueFind\RecordDataFormatter\Specs\DefaultRecord' => 'VuFind\RecordDataFormatter\Specs\DefaultRecordFactory',
                ],
                'aliases' => [
                    'DefaultRecord' => 'TueFind\RecordDataFormatter\Specs\DefaultRecord',
                    'VuFind\RecordDataFormatter\Specs\DefaultRecord' => 'TueFind\RecordDataFormatter\Specs\DefaultRecord',
                ],
            ],
            'recorddriver' => [
                'factories' => [
                    'TueFind\RecordDriver\SolrAuthDefault' => 'VuFind\RecordDriver\SolrDefaultWithoutSearchServiceFactory',
                    'TueFind\RecordDriver\SolrAuthMarc' => 'VuFind\RecordDriver\SolrDefaultWithoutSearchServiceFactory',
                    'TueFind\RecordDriver\SolrDefault' => 'TueFind\RecordDriver\SolrDefaultFactory',
                    'TueFind\RecordDriver\SolrMarc' => 'TueFind\RecordDriver\SolrMarcFactory',
                ],
                'delegators' => [
                    'TueFind\RecordDriver\SolrMarc' => 'VuFind\RecordDriver\IlsAwareDelegatorFactory',
                ],
                'aliases' => [
                    'solrauth' => 'TueFind\RecordDriver\SolrAuthMarc',
                    'solrauthdefault' => 'TueFind\RecordDriver\SolrAuthMarc',
                    'solrauthmarc' => 'TueFind\RecordDriver\SolrAuthMarc',
                    'solrdefault' => 'TueFind\RecordDriver\SolrDefault',
                    'solrmarc' => 'TueFind\RecordDriver\SolrMarc',
                    'search3default' => 'TueFind\RecordDriver\Search3Default',
                ],
            ],
            'recordtab' => [
                'factories' => [
                    'TueFind\RecordTab\AuthorityNameVariants' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                    'TueFind\RecordTab\ItemFulltextSearch' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                ],
                'aliases' => [
                    'AuthorityNameVariants' => 'TueFind\RecordTab\AuthorityNameVariants',
                    'ItemFulltextSearch' => 'TueFind\RecordTab\ItemFulltextSearch',
                ],
            ],
            'search_backend' => [
                'factories' => [
                    'SolrAuth' => 'TueFind\Search\Factory\SolrAuthBackendFactory',
                    'Search3' => 'TueFind\Search\Factory\Search3BackendFactory',
                ],
                'aliases' => [

                ],
            ],
            'search_options' => [
                'factories' => [
                    'solrauthorfacets' => 'VuFind\Search\Options\OptionsFactory',
                ],
                'aliases' => [
                    'search2' => 'TueFind\Search\Search2\Options',
                    'search3' => 'TueFind\Search\Search3\Options',
                    'solrauthorfacets' => 'TueFind\Search\SolrAuthorFacets\Options',
                ],
            ],
            'search_params' => [
                'factories' => [
                    'TueFind\Search\SolrAuthorFacets\Params' => 'VuFind\Search\Solr\ParamsFactory',
                ],
                'aliases' => [
                    'search3' => 'TueFind\Search\Search3\Params',
                    'solr' => 'TueFind\Search\Solr\Params',
                    'solrauthorfacets' => 'TueFind\Search\SolrAuthorFacets\Params',
                ],
            ],
            'search_results' => [
                'factories' => [
                    'TueFind\Search\Search3\Results' => 'TueFind\Search\Search3\ResultsFactory',
                    'TueFind\Search\Solr\Results' => 'VuFind\Search\Solr\ResultsFactory',
                    'TueFind\Search\SolrAuth\Results' => 'VuFind\Search\Solr\ResultsFactory',
                    'TueFind\Search\SolrAuthorFacets\Results' => 'VuFind\Search\Solr\ResultsFactory',
                ],
                'aliases' => [
                    'search3' => 'TueFind\Search\Search3\Results',
                    'solr' => 'TueFind\Search\Solr\Results',
                    'solrauth' => 'TueFind\Search\SolrAuth\Results',
                    'solrauthorfacets' => 'TueFind\Search\SolrAuthorFacets\Results',
                ],
            ],
            'sitemap' => [
                'factories' => [
                    'TueFind\Sitemap\Plugin\Index' => 'TueFind\Sitemap\Plugin\IndexFactory',
                ],
                'aliases' => [
                    'Index' => 'TueFind\Sitemap\Plugin\Index',
                ],
            ],
        ],
    ],
    'lmc_rbac' => [
        'assertion_manager' => [
            'factories' => [
                'TueFind\Role\Assertion\HasCmsRightsAssertion' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                'TueFind\Role\Assertion\HasUserAuthoritiesRightsAssertion' => 'Laminas\ServiceManager\Factory\InvokableFactory',
                'TueFind\Role\Assertion\IsAdminAssertion' => 'Laminas\ServiceManager\Factory\InvokableFactory',
            ],
            'aliases' => [
                'HasCmsRights' => 'TueFind\Role\Assertion\HasCmsRightsAssertion',
                'HasUserAuthoritiesRights' => 'TueFind\Role\Assertion\HasUserAuthoritiesRightsAssertion',
                'IsAdmin' => 'TueFind\Role\Assertion\IsAdminAssertion',
            ],
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
    'MyResearch/RssFeedPreview',
    'MyResearch/RssFeedSettings',
    'MyResearch/SelfArchiving',
    'RssFeed/Full',
    'Search3/FacetList',
    'Search3/Home',
    'Search3/Results',
    'Search3/Versions',
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addNonTabRecordActions($config, $nonTabRecordActions);
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
