<?php
return [
    'extends' => 'tuefind2',
    'favicon' => 'krimdok-favicon.ico',
    'js' => [
        'overrides.js',
        'vendor/resultGrouping.js',
    ],
    'helpers' => [
        'factories' => [
            'TueFind\View\Helper\Root\RecordDataFormatter' => 'KrimDok\View\Helper\Root\RecordDataFormatterFactory',
        ],
        'aliases' => [
            
        ],
    ],
];
