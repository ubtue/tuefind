<?php

return [
    'extends' => 'bootstrap3',
    'helpers' => [
        'factories' => [
            'TueFind\View\Helper\Root\AccountMenu' => 'VuFind\View\Helper\Root\AccountMenuFactory',
            'TueFind\View\Helper\Root\Matomo' => 'TueFind\View\Helper\Root\MatomoFactory',
            'TueFind\View\Helper\Root\Record' => 'VuFind\View\Helper\Root\RecordFactory',
            'TueFind\View\Helper\Root\RecordDataFormatter' => 'TueFind\View\Helper\Root\RecordDataFormatterFactory',
            'TueFind\View\Helper\Root\SearchTabs' => 'VuFind\View\Helper\Root\SearchTabsFactory',
            'TueFind\View\Helper\Root\Url' => 'VuFind\View\Helper\Root\UrlFactory',
            'TueFind\View\Helper\TueFind\Authority' => 'TueFind\View\Helper\TueFind\AuthorityFactory',
            'TueFind\View\Helper\TueFind\TueFind' => 'TueFind\View\Helper\TueFind\Factory',

            // special overrides related to VuFindTheme\Module.php
            'TueFind\View\Helper\SetupThemeResources' => 'VuFindTheme\View\Helper\SetupThemeResourcesFactory',
        ],
        'aliases' => [
            'accountMenu' => 'TueFind\View\Helper\Root\AccountMenu',
            'authority' => 'TueFind\View\Helper\TueFind\Authority',
            'matomo' => 'TueFind\View\Helper\Root\Matomo',
            'record' => 'TueFind\View\Helper\Root\Record',
            'recordDataFormatter' => 'TueFind\View\Helper\Root\RecordDataFormatter',
            'searchTabs' => 'TueFind\View\Helper\Root\SearchTabs',
            'url' => 'TueFind\View\Helper\Root\Url',
            'Url' => 'TueFind\View\Helper\Root\Url',

            'tuefind' => 'TueFind\View\Helper\TueFind\TueFind',

            // special overrides related to VuFindTheme\Module.php
            'setupThemeResources' => 'TueFind\View\Helper\SetupThemeResources',
        ],
    ],
    'css' => [
        'vendor/jquery-ui.min.css',
        'vendor/jquery.dataTable.css',
        'botprotect.css',
    ],
    'js' => [
        'tuefind.js',
        'vendor/jquery-ui.min.js',
        'vendor/jquery.dataTable.js',
        'vendor/resultGrouping.js',
    ],
    'icons' => [
        'upload' => 'FontAwesome:upload',
        'user-plus' => 'FontAwesome:user-plus',
        'user-secret' => 'FontAwesome:user-secret',
    ],
];
