<?php
return [
    'slideable' => true,
    'nav' => [
        [
            'title' => 'Example Link', 
            'href'  => '#', 
            'class' => 'active',
            'sub'   =>
                [
                    [
                        'title' => 'Example Link', 
                        'href'  => '#', 
                        'class' => ''
                    ],
                    [
                        'title' => 'Example Link', 
                        'href'  => '#', 
                        'class' => 'active'
                    ],
                    [
                        'title' => 'Example Link', 
                        'href'  => '#', 
                        'class' => ''
                    ]
                ]
        ],
        [
            'title' => 'Example Link', 
            'href'  => '#', 
            'class' => '',
            'sub'   => []
        ],
        [
            'title' => 'Example Link', 
            'href'  => '#', 
            'class' => '',
            'sub'   =>
                [
                    [
                        'title' => 'Example Link', 
                        'href'  => '#', 
                        'class' => ''
                    ],
                    [
                        'title' => 'Example Link', 
                        'href'  => '#', 
                        'class' => ''
                    ],
                    [
                        'title' => 'Example Link', 
                        'href'  => '#', 
                        'class' => ''
                    ]
                ]
        ]
    ],
    'filterModules' => [
        [
            'title' => 'Saved Filters',
            'nav'   => 
                [
                    [
                        'title' => 'New Orders', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ],
                    [
                        'title' => 'International', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ],
                    [
                        'title' => 'Processing', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ],
                    [
                        'title' => 'Dispatched', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ]
                ]
        ],
        [
            'title' => 'Batches',
            'nav'   => 
                [
                    [
                        'title' => 'Batch 100', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ],
                    [
                        'title' => 'Batch 99', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ],
                    [
                        'title' => 'Batch 98', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ],
                    [
                        'title' => 'Batch 97', 
                        'href'  => '#', 
                        'class' => '',
                        'totals' => [
                            ['class' => 'red', 'total' => '2'],
                            ['class' => 'green', 'total' => '12'],
                        ]
                    ]
                ]
        ]
    ]
];