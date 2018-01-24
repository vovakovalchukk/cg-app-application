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
        $accountId = $this->params()->fromRoute('accountId');
        $externalCategoryId = $this->params()->fromRoute('externalCategoryId');
        /** @var Account $account */
        $account = $this->accountService->fetch($accountId);
        $categoryFields = [
            $externalCategoryId => [
                'listingDuration' => $this->service->getListingDurationsForCategory($account, $externalCategoryId)
            ]
        ];
        return $this->buildResponse($categoryFields);
    }

    public function defaultSettingsAjaxAction()
    {
        $accountId = $this->params()->fromRoute('accountId');

        try {
            $defaultSettings = $this->createListingsService->fetchDefaultSettingsForAccount($accountId);
            if (empty(array_filter($defaultSettings))) {
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
            return $this->buildErrorResponse(['Invalid accountId provided on the post data']);
        }

        try {
            /** @var Account $account */
            $account = $this->accountService->fetch($accountId);
            if ($account->getChannel() !== 'ebay') {
                return $this->buildErrorResponse('The account with ID ' . $accountId . ' must be an eBay account', ['categories' => []]);
            }
        } catch (NotFound $e) {
            return $this->buildErrorResponse('Account with ID ' . $accountId . ' doesn\'t exist.');
        }
        return $this->buildResponse([
            'category' => $this->service->getCategoryOptionsForAccount($account),
            'shippingService' => $this->service->getShippingMethodsForAccount($account),
            'currency' => $this->service->getCurrencySymbolForAccount($account)
        ]);
    }

    public function categoryChildrenAction()
    {
        $accountId = $this->params()->fromRoute('accountId');
        $externalCategoryId = $this->params()->fromRoute('externalCategoryId');
        try {
            /** @var Account $account */
            $account = $this->accountService->fetch($accountId);
            if ($account->getChannel() !== 'ebay') {
                return $this->buildErrorResponse('The account with ID ' . $accountId . ' must be an eBay account', ['categories' => []]);
            }
        } catch (NotFound $e) {
            return $this->buildErrorResponse(
                'Account with ID ' . $accountId . ' doesn\'t exist.',
                ['categories' => []]
            );
        }
        return $this->buildResponse([
            'categories' => $this->service->getCategoryChildrenForCategoryAndAccount($account, $externalCategoryId)
        ]);
    }
}
