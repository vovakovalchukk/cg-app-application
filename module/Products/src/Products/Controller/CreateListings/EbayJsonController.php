<?php

namespace Products\Controller\CreateListings;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use Application\Controller\AbstractJsonController;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Create\Ebay\Service;
use Products\Listing\Create\Service as CreateListingsService;

class EbayJsonController extends AbstractJsonController
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
        parent::__construct($jsonModelFactory);
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
            $defaultSettings = $this->createListingsService->fetchDefaultSettingsForAccount($accountId);
            if (empty($defaultSettings)) {
                return $this->buildErrorResponse('NO_SETTINGS');
            }
            return $this->buildResponse($defaultSettings);
        } catch (NotFound $e) {
            return $this->buildErrorResponse('The account ' . $accountId . ' could not be found.');
        } catch (\Exception $e) {
            return $this->buildErrorResponse('An error has occurred. Please try again');
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
