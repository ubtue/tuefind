<?php
return [
    'js' => [
        ['file' => 'collapse_expand.js', 'priority' => 600],
        ],
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