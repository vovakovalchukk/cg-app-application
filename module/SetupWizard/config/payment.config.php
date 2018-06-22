<?php
use CG_Billing\Controller\PaymentJsonController as BillingPaymentJson;
use CG_GoCardless\Controller\RedirectFlowController as GoCardlessRedirectFlow;
use CG_Sagepay\Controller\ChangeController as SagepayChange;
use SetupWizard\Controller\PaymentController;
use SetupWizard\Module;

return [
    'SetupWizard' => [
        'SetupWizard' => [
            'white_listed_routes' => [
                BillingPaymentJson::ROUTE_PAYMENT_SELECTION => true,
                GoCardlessRedirectFlow::ROUTE_SUCCESS => true,
                SagepayChange::ROUTE_SAGEPAY . '/' . SagepayChange::ROUTE_SAGEPAY_CHANGE => true,
                SagepayChange::ROUTE_SAGEPAY . '/' . SagepayChange::ROUTE_SAGEPAY_REDIRECT => true,
            ],
        ],
    ],
    'di' => [
        'instance' => [
            GoCardlessRedirectFlow::class => [
                'parameters' => [
                    'redirectRoute' => Module::ROUTE . '/' . PaymentController::ROUTE_PAYMENT,
                ],
            ],
        ],
    ],
];