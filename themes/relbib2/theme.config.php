<?php
return [
    'extends' => 'ixtheo2',
    'favicon' => 'relbib-favicon.ico',
    'js' => [
        'relbib2.js',
    ],
    'helpers' => [
        'factories' => [
            'IxTheo\View\Helper\IxTheo\RelBib' => 'IxTheo\View\Helper\IxTheo\Factory'
        ],
        'aliases' => [
            'relbib' => 'IxTheo\View\Helper\IxTheo\RelBib',
            'RelBib' => 'IxTheo\View\Helper\IxTheo\RelBib',
        ],
    ]
];
