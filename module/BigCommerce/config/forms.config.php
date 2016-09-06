<?php
use CG\BigCommerce\Account\CreationService as BigCommerceAccountCreator;
use CG\Channel\Service as ChannelService;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;

return [
    'forms' => [
        ChannelService::FORM_SETTINGS_ACCOUNT_PREFIX . \CG\Stdlib\hyphenToClassname(BigCommerceAccountCreator::CHANNEL) => [
            'attributes' => array(
                'id' => 'settings_account_form_bigcommerce'
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
