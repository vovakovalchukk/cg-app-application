<?php
require dirname(__DIR__) . '/di/components.php';

$definitions = array();
foreach ($components as $component) {
    $definitions[] = dirname(dirname(__DIR__)) . '/data/di/' . $component . '-definition.php';
}

return array(
    'di' => array(
        'definition' => array(
            'runtime' => array(
                'enabled' => true
            ),
            'compiler' => $definitions
        )
    )
);