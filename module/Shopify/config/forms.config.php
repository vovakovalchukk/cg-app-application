<?php
use CG\Channel\Service as ChannelService;
use CG\Shopify\Account\CreationService as ShopifyAccountCreator;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . \CG\Stdlib\hyphenToClassname(ShopifyAccountCreator::CHANNEL) => [
            'attributes' => array(
                'id' => 'settings_account_form_shopify'
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
