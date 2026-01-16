<?php
return [
    'js' => ['collapse_expand.js'],
    'helpers' => [
        'factories' => [
            'VuFindCollapseExpand\View\Helper\CollapseExpand\CollapseExpand' => 'VuFindCollapseExpand\View\Helper\CollapseExpand\CollapseExpandFactory',
        ],
        'initializers' => [
            'VuFindCollapseExpand\ServiceManager\ServiceInitializer',
        ],
        'aliases' => [
            'collapseExpand' => 'VuFindCollapseExpand\View\Helper\CollapseExpand\CollapseExpand',
        ],
    ],

];