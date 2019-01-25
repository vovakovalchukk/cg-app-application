<?php
use CG\Channel\Service as ChannelService;
use CG\Walmart\Account\CreationService as AccountCreator;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use function CG\Stdlib\hyphenToClassname;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . hyphenToClassname(AccountCreator::CHANNEL) => [
            'attributes' => [
                'id' => 'settings_account_form_walmart',
            ],
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
                [
                    'spec' => [
                        'name' => 'renew',
                        'options'=> [
                            'label' => 'Renew Connection'
                        ],
                        'type'  => Submit::class,
                        'attributes' => [
                            'value' => 'Renew Connection',
                            'class' => 'button'
                        ],
                    ]
                ],
            ]
        ]
    ]
];
