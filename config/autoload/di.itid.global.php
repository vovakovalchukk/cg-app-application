<?php

use CG\Log\ItidService;

return array(
    'di' => array(
        'instance' => array(
            ItidService::class => array(
                'shared' => true
            )
        )
    )
);
