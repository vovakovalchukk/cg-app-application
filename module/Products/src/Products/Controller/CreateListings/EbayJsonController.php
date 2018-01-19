<?php

namespace Products\Controller\CreateListings;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Create\Ebay\Service;
use Products\Listing\Create\Service as CreateListingsService;
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
    /** @var CreateListingsService */
    protected $createListingsService;
    /** @var AccountService */
    protected $accountService;
    /** @var Service */
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        CreateListingsService $createListingsService,
        AccountService $accountService,
        Service $service
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->createListingsService = $createListingsService;
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
        $accountId = $this->params()->fromRoute('accountId');

        try {
            return $this->jsonModelFactory->newInstance(
                $this->createListingsService->fetchDefaultSettingsForAccount($accountId)
            );
        } catch (\Exception $e) {
            return $this->jsonModelFactory->newInstance([]);
        }
    }

    public function channelSpecificFieldValuesAction()
    {
        /** @TODO: remove the hard-coded account Id and sanitize the accountId from post*/
        $accountId = $this->params()->fromQuery('accountId') ?? 23;
        /** @var Account $account */
        $account = $this->accountService->fetch($accountId);

        return $this->jsonModelFactory->newInstance([
            'category' => $this->service->getCategoryOptionsForAccount($account),
            'shippingService' => $this->service->getShippingMethodsForAccount($account),
            'currency' => $this->service->getCurrencySymbolForAccount($account)
        ]);
    }
}
