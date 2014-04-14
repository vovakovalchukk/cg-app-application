<?php
$definitions = array(
    dirname(dirname(__DIR__)) . '/data/di/di-definition.php',
    dirname(dirname(__DIR__)) . '/data/di/php_internal-definition.php'
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