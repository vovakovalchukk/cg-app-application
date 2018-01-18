<?php

namespace Products\Controller\CreateListings;

use CG\Account\Client\Service as AcountService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Create\Ebay\Service;
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
    /** @var AcountService */
    protected $accountService;
    /** @var Service */
    protected $service;

    public function __construct(JsonModelFactory $jsonModelFactory, AcountService $accountService, Service $service)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->accountService = $accountService;
        $this->service = $service;
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
        //$accountId = $this->params()->fromPost('accountId');
        $accountId = $this->params()->fromQuery('accountId'); // TEMP!
        $account = $this->accountService->fetch($accountId);

        return $this->jsonModelFactory->newInstance([
            'category' => $this->service->getCategoryOptionsForAccount($account),
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