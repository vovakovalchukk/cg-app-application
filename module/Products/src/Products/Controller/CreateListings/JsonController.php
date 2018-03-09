<?php

namespace Products\Controller\CreateListings;

use Application\Controller\AbstractJsonController;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Permission\Exception as PermissionException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Channel\CategoriesRefreshInterface;
use Products\Listing\Channel\CategoryChildrenInterface;
use Products\Listing\Channel\CategoryDependentServiceInterface;
use Products\Listing\Channel\ChannelSpecificValuesInterface;
use Products\Listing\Channel\DefaultAccountSettingsInterface;
use Products\Listing\Channel\Factory as CreateListingsFactory;
use Products\Listing\Exception as ListingException;

class JsonController extends AbstractJsonController
{
    const ROUTE_CREATE_LISTINGS = 'CreateListings';
    const ROUTE_DEFAULT_SETTINGS = 'DefaultSettings';
    const ROUTE_CATEGORY_DEPENDENT_FIELD_VALUES = 'CategoryDependentFieldValues';
    const ROUTE_ACCOUNT_SPECIFIC_FIELD_VALUES = 'AccountSpecificFieldValues';
    const ROUTE_CATEGORY_CHILDREN = 'CategoryChildren';
    const ROUTE_REFRESH_CATEGORIES = 'RefreshCategories';

    /** @var AccountService */
    protected $accountService;
    /** @var CreateListingsFactory */
    protected $factory;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        AccountService $accountService,
        CreateListingsFactory $factory
    ) {
        parent::__construct($jsonModelFactory);
        $this->accountService = $accountService;
        $this->factory = $factory;
    }

    public function defaultSettingsAjaxAction()
    {
        try {
            $account = $this->fetchAccountFromRoute();
            /** @var DefaultAccountSettingsInterface $service */
            $service = $this->fetchAndValidateChannelService($account, DefaultAccountSettingsInterface::class);
            $defaultSettings = $service->getDefaultSettingsForAccount($account);
            if (empty(array_filter($defaultSettings))) {
                return $this->buildErrorResponse('NO_SETTINGS');
            }
            return $this->buildResponse($defaultSettings);
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function channelSpecificFieldValuesAction()
    {
        try {
            $account = $this->fetchAccountFromRoute();
            /** @var ChannelSpecificValuesInterface $service */
            $service = $this->fetchAndValidateChannelService($account, ChannelSpecificValuesInterface::class);
            return $this->buildResponse($service->getChannelSpecificFieldValues($account));
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function categoryDependentFieldValuesAction()
    {
        try {
            $account = $this->fetchAccountFromRoute();
            /** @var CategoryDependentServiceInterface $service */
            $service = $this->fetchAndValidateChannelService($account, CategoryDependentServiceInterface::class);
            return $this->buildResponse(
                $service->getCategoryDependentValues(
                    $account, $this->getCategoryIdFromRoute()
                )
            );
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function categoryChildrenAction()
    {
        try {
            $account = $this->fetchAccountFromRoute();
            /** @var CategoryChildrenInterface $service */
            $service = $this->fetchAndValidateChannelService($account, CategoryChildrenInterface::class);
            return $this->buildResponse([
                'categories' => $service->getCategoryChildrenForCategoryAndAccount(
                    $account, $this->getCategoryIdFromRoute()
                )
            ]);
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function refreshCategoriesAction()
    {
        try {
            $account = $this->fetchAccountFromRoute();
            /** @var CategoriesRefreshInterface $service */
            $service = $this->fetchAndValidateChannelService($account, CategoriesRefreshInterface::class);
            return $this->buildResponse([
                'categories' => $service->refetchAndSaveCategories($account)
            ]);
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    protected function fetchAccountFromRoute(): Account
    {
        return $this->fetchAccountById($this->params()->fromRoute('accountId', 0));
    }

    protected function fetchAccountById(int $accountId): Account
    {
        try {
            return $this->accountService->fetch($accountId);
        } catch (NotFound $e) {
            throw new ListingException('The account ' . $accountId . ' could not be found.');
        } catch (PermissionException $e) {
            throw new ListingException('The account ID ' . $accountId . ' is not valid');
        }
    }

    protected function buildGenericErrorResponse()
    {
        return $this->buildErrorResponse('An error has occurred. Please try again');
    }

    protected function fetchAndValidateChannelService(Account $account, string $className)
    {
        try {
            $service = $this->factory->buildChannelService($account, $this->params()->fromPost());
            if ($service instanceof $className) {
                return $service;
            }
            throw new ListingException('The account with ID ' . $account->getId() . ' does not support this action.');
        } catch (\InvalidArgumentException $e) {
            throw new ListingException('The account with ID ' . $account->getId() . ' is not valid');
        }
    }

    protected function getCategoryIdFromRoute(): string
    {
        return (int) $this->params()->fromRoute('categoryId', -1);
    }
}
