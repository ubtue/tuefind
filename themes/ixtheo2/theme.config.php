<?php
return [
    'extends' => 'tuefind2',
    'favicon' => 'ixtheo-favicon.ico',
    // Note: leaflet is included in original bootstrap3+bootstrap5 themes, but inactive there
    'css' => [
        ['file' => 'vendor/cw/swiper11.css'],
        ['file' => 'vendor/leaflet/leaflet.css'],
    ],
    'js' => [
        ['file' => 'ixtheo.js', 'priority' => 1000],
        ['file' => 'ixtheo2.js', 'priority' => 900],
        ['file' => 'vendor/cw/swiper11.js', 'priority' => 800],
        ['file' => 'vendor/cw/smooth-scroll.js', 'priority' => 700],
        ['file' => 'vendor/leaflet/leaflet.js', 'priority' => 600],
    ],
    'helpers' => [
        'factories' => [
            'TueFind\View\Helper\Root\RecordDataFormatter' => 'IxTheo\View\Helper\Root\RecordDataFormatterFactory',
            'IxTheo\View\Helper\Root\Browse' => 'Laminas\ServiceManager\Factory\InvokableFactory',
            'IxTheo\View\Helper\Root\Citation' => 'VuFind\View\Helper\Root\CitationFactory',
            'IxTheo\View\Helper\Root\Record' => 'VuFind\View\Helper\Root\RecordFactory',
            'IxTheo\View\Helper\TueFind\Authority' => 'TueFind\View\Helper\TueFind\AuthorityFactory',
            'IxTheo\View\Helper\IxTheo\IxTheo' => 'IxTheo\View\Helper\IxTheo\Factory',
            'VuFindCollapseExpand\View\Helper\CollapseExpand\CollapseExpand' => 'VuFindCollapseExpand\View\Helper\CollapseExpand\CollapseExpandFactory'
        ],
        'initializers' => [
            'VuFindCollapseExpand\ServiceManager\ServiceInitializer'
        ],
        'aliases' => [
            'authority' => 'IxTheo\View\Helper\TueFind\Authority',
            'browse' => 'IxTheo\View\Helper\Root\Browse',
            'citation' => 'IxTheo\View\Helper\Root\Citation',
            'record' => 'IxTheo\View\Helper\Root\Record',
            'ixtheo' => 'IxTheo\View\Helper\IxTheo\IxTheo',
            'IxTheo' => 'IxTheo\View\Helper\IxTheo\IxTheo',
            'collapseExpand' => 'VuFindCollapseExpand\View\Helper\CollapseExpand\CollapseExpand'
        ],
    ],
];