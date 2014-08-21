<?php
use CG_UI\Form\Element\Button\Save;
use Settings\Controller\ChannelController;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;

return [
    'forms' => [
        ChannelController::ACCOUNT_DETAIL_FORM => [
            'attributes' => array(
                'id' => 'account_details_form'
            ),
            'elements' => [
                [
                    'spec' => [
                        'type' => Csrf::class,
                        'name' => 'csrf',
                        'options' => [
                            'csrf_options' => [
                                'timeout' => 43200
                            ]
                        ],
                    ]
                ],
                [
                    'spec' => [
                        'type' => Text::class,
                        'required' => 'true',
                        'name' => 'displayName',
                        'options' => [
                            'label' => 'Display Name'
                        ],
                        'attributes' => array(
                            'required' => 'required'
                        )
                    ]
                ],
                [
                    'spec' => [
                        'type' => Hidden::class,
                        'required' => 'true',
                        'name' => 'organisationUnitId',
                        'attributes' => array(
                            'required' => 'required'
                        )
                    ]
                ],
                [
                    'spec' => [
                        'name' => 'save',
                        'options'=> [
                            'label' => 'Save'
                        ],
                        'type'  => 'Submit',
                        'attributes' => [
                            'value' => 'Save',
                            'class' => 'button'
                        ],
                    ]
                ],
            ]
        ],
    ],
];
