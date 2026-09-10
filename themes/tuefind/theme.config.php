<?php

return [
    'extends' => 'bootstrap5',
    'helpers' => [
        'factories' => [
            'TueFind\View\Helper\Root\Matomo' => 'TueFind\View\Helper\Root\MatomoFactory',
            'TueFind\View\Helper\Root\Record' => 'VuFind\View\Helper\Root\RecordFactory',
            'TueFind\View\Helper\Root\SearchTabs' => 'VuFind\View\Helper\Root\SearchTabsFactory',
            'TueFind\View\Helper\Root\Url' => 'VuFind\View\Helper\Root\UrlFactory',
            'TueFind\View\Helper\TueFind\Authority' => 'TueFind\View\Helper\TueFind\AuthorityFactory',
            'TueFind\View\Helper\TueFind\TueFind' => 'TueFind\View\Helper\TueFind\Factory',

            // special overrides related to VuFindTheme\Module.php
            'TueFind\View\Helper\SetupThemeResources' => 'VuFindTheme\View\Helper\SetupThemeResourcesFactory',
        ],
        'aliases' => [
            'authority' => 'TueFind\View\Helper\TueFind\Authority',
            'matomo' => 'TueFind\View\Helper\Root\Matomo',
            'record' => 'TueFind\View\Helper\Root\Record',
            'searchTabs' => 'TueFind\View\Helper\Root\SearchTabs',
            'url' => 'TueFind\View\Helper\Root\Url',
            'Url' => 'TueFind\View\Helper\Root\Url',

            'tuefind' => 'TueFind\View\Helper\TueFind\TueFind',

            // special overrides related to VuFindTheme\Module.php
            'setupThemeResources' => 'TueFind\View\Helper\SetupThemeResources',
        ],
    ],
    'css' => [
        ['file' => 'vendor/jquery-ui.min.css'],
        ['file' => 'vendor/jquery.dataTable.css'],
        ['file' => 'vendor/sum_lite.css'],
        ['file' => 'vendor/fontawesome-free-6.7.2-web/css/all.min.css'],
    ],
    'js' => [
        ['file' => 'tuefind.js', 'priority' => 1500],
        ['file' => 'vendor/jquery-ui.min.js', 'priority' => 1400],
        ['file' => 'vendor/jquery.dataTable.js', 'priority' => 1300],
        ['file' => 'vendor/summernote_lite.js', 'priority' => 1350],

    ],
    'icons' => [
        'sets' => [
            'FontAwesome6' => [
                'template' => 'font',
                'prefix'   => '',
            ],
        ],
        'aliases' => [
            'upload'      => 'FontAwesome:upload',
            'user-plus'   => 'FontAwesome:user-plus',

            // --- add new icon from FA6 ---
            'github'      => 'FontAwesome6:fab fa-github',
            'discord'     => 'FontAwesome6:fab fa-discord',

            //(Solid)
            'robot'       => 'FontAwesome6:fas fa-robot',
            'brain'       => 'FontAwesome6:fas fa-brain',
            'sparkles'    => 'FontAwesome6:fas fa-sparkles',
            'graduation'  => 'FontAwesome6:fas fa-graduation-cap',

            //(Regular)
            'face-smile'  => 'FontAwesome6:far fa-face-smile',

            'truck'  => 'FontAwesome6:fas fa-truck',
        ],
    ],
    'mixins' => [
        'vufind-beacon-finder',
        'vufind-collapse-expand',
    ],
];
