<?php

use CG\Stock\Auditor;

return array(
    'di' => array(
        'instance' => array(
            Auditor::class => array(
                'shared' => true
            )
        )
    )
);
