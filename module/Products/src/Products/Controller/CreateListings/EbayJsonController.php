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
    const ROUTE_CATEGORY_CHILDREN = 'CategoryChildren';

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
        $categoryFields = [
            $externalCategoryId => [
                'listingDuration' => $this->service->getListingDurationsForCategory($externalCategoryId)
            ]
        ];
        return $this->jsonModelFactory->newInstance($categoryFields);
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
        $accountId = intval($this->params()->fromPost('accountId'));
        if ($accountId === 0) {
            return $this->jsonModelFactory->newInstance([]);
        }
        /** @var Account $account */
        $account = $this->accountService->fetch($accountId);

        return $this->jsonModelFactory->newInstance([
            'category' => $this->service->getCategoryOptionsForAccount($account),
            'shippingService' => $this->service->getShippingMethodsForAccount($account),
            'currency' => $this->service->getCurrencySymbolForAccount($account)
        ]);
    }

    public function categoryChildrenAction()
    {
        $externalCategoryId = $this->params()->fromRoute('externalCategoryId');
        return $this->jsonModelFactory->newInstance([
            'categories' => $this->service->getCategoryChildrenForCategory($externalCategoryId)
        ]);
    }
}
