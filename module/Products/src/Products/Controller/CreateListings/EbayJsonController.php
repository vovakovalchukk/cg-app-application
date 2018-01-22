<?php

namespace Products\Controller\CreateListings;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class EbayJsonController extends AbstractActionController
{
    const ROUTE_CREATE_LISTINGS = 'CreateListings';
    const ROUTE = 'EbayListings';
    const ROUTE_DEFAULT_SETTINGS = 'DefaultSettings';
    const ROUTE_CATEGORY_DEPENDENT_FIELD_VALUES = 'CategoryDependentFieldValues';
    const ROUTE_ACCOUNT_SPECIFIC_FIELD_VALUES = 'AccountSpecificFieldValues';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function categoryDependentFieldValuesAction()
    {
        $externalCategoryId = $this->params()->fromRoute('externalCategoryId');
        $dummyCategoryIdToFields = [
            1 => [
                'listingDuration' => [
                    '1 days',
                    '2 days',
                    '3 days'
                ]
            ],
            2 => [
                'listingDuration' => [
                    '4 hours',
                    '5 hours',
                    '6 hours'
                ]
            ],
            3 => [
                'listingDuration' => [
                    '4 months',
                    '5 months',
                    '6 months'
                ]
            ]
        ];
        return $this->jsonModelFactory->newInstance($dummyCategoryIdToFields[$externalCategoryId]);
    }

    public function defaultSettingsAjaxAction()
    {
        $accountId = $this->params()->fromRoute('accountId');

        return $this->jsonModelFactory->newInstance([
            'listingLocation' => 'Jupiter',
            'listingCurrency' => 'GBP',
            'paypalEmail' => 'dev+createlistingstest@channelgrabber.com',
            'listingDuration' => 'GTC',
            'listingDipsatchTime' => 12,
            'listingPaymentMethod' => [
                'payPal', 'cheque'
            ]
        ]);
    }

    public function channelSpecificFieldValuesAction()
    {
        return $this->jsonModelFactory->newInstance([
            'category' => [
                1 => 'spuds',
                2 => 'bananas',
                3 => 'bicycles'
            ],
            'shippingService' => [
                1 => 'Royal Snail',
                2 => 'Parcel Power',
                3 => 'Carrier Pidgeon'
            ],
            'currency' => [
                'BTC',
                'ETH',
                'XRP'
            ]
        ]);
    }
}