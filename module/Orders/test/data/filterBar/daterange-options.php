<?php
$dateFormat = 'd/m/y';
return [
    [
        'title' => 'All Time',
        'from' => 'All',
        'to' => 'All'
    ],
    [
        'title' => 'Today',
        'from' => date($dateFormat),
        'to' => date($dateFormat)
    ],
    [
        'title' => 'Last 7 days',
        'from' => date($dateFormat, strtotime("-7 days")),
        'to' => date($dateFormat)
    ],
    [
        'title' => 'Month to date',
        'from' => date($dateFormat, strtotime('first day of ' . date('F Y'))),
        'to' => date($dateFormat)
    ],
    [
        'title' => 'Year to date',
        'from' => date($dateFormat, strtotime('first day of January ' . date('Y'))),
        'to' => date($dateFormat)
    ],
    [
        'title' => 'The previous month',
        'from' => date($dateFormat, strtotime('first day of last month ')),
        'to' => date($dateFormat, strtotime('last day of last month ')),
    ]
];