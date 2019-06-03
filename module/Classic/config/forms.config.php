<?php
use CG\Channel\Service as ChannelService;
use Zend\Form\Element\Hidden;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . 'Classic' => [
            'attributes' => array(
                'id' => 'settings_account_form_classic'
            ),
            'elements' => [
                [
                    'spec' => [
                        'name' => 'account',
                        'type'  => Hidden::class,
                    ]
                ],
                [
                    'spec' => [
                        'name' => 'route',
                        'type'  => Hidden::class,
                    ]
                ],
            ]
        ]
    ]
];
