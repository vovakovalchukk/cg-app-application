<?php
require dirname(__DIR__) . '/di/components.php';

$definitions = array(
    dirname(dirname(__DIR__)) . '/data/di/di-definition.php'
);

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