<?php
use CG\Channel\Service as ChannelService;
use Zend\Form\Element\Hidden;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . hyphenToFullyQualifiedClassname('royal-mail-click-drop', 'CourierExport') => [
            'attributes' => [
                'id' => 'settings_account_form_royal-mail-click-drop'
            ],
            'elements' => [
                [
                    'spec' => [
                        'name' => 'account',
                        'type'  => Hidden::class,
                    ],
                ],
                [
                    'spec' => [
                        'name' => 'route',
                        'type'  => Hidden::class,
                    ],
                ],
            ],
        ],
    ],
];
