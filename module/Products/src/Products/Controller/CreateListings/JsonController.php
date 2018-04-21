<?php
namespace Products\Controller\CreateListings;

use Application\Controller\AbstractJsonController;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Permission\Exception as PermissionException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\Listing\Channel\Service as ChannelService;
use Products\Listing\Exception as ListingException;

class JsonController extends AbstractJsonController
{
    const ROUTE_CREATE_LISTINGS = 'CreateListings';
    const ROUTE_DEFAULT_SETTINGS = 'DefaultSettings';
    const ROUTE_CATEGORY_DEPENDENT_FIELD_VALUES = 'CategoryDependentFieldValues';
    const ROUTE_CATEGORY_TEMPLATE_DEPENDENT_FIELD_VALUES = 'CategoryTemplateDependentFieldValues';
    const ROUTE_ACCOUNT_SPECIFIC_FIELD_VALUES = 'AccountSpecificFieldValues';
    const ROUTE_CATEGORY_CHILDREN = 'CategoryChildren';
    const ROUTE_REFRESH_CATEGORIES = 'RefreshCategories';

    /** @var AccountService */
    protected $accountService;
    /** @var ChannelService */
    protected $channelService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        AccountService $accountService,
        ChannelService $channelService
    ) {
        parent::__construct($jsonModelFactory);
        $this->accountService = $accountService;
        $this->channelService = $channelService;
    }

    public function defaultSettingsAjaxAction()
    {
        try {
            $defaultSettings = $this->channelService->getDefaultSettingsForAccount($this->fetchAccountFromRoute());
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
            return $this->buildResponse($this->channelService->getChannelSpecificFieldValues(
                $this->fetchAccountFromRoute()
            ));
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function categoryDependentFieldValuesAction()
    {
        try {
            return $this->buildResponse(
                $this->channelService->getCategoryDependentValues(
                    $this->fetchAccountFromRoute(), $this->getCategoryIdFromRoute()
                )
            );
        } catch (ListingException $e) {
            return $this->buildErrorResponse($e->getMessage());
        } catch (\Throwable $e) {
            return $this->buildGenericErrorResponse();
        }
    }

    public function categoryTemplateDependentFieldValuesAction()
    {
        // Dummy Data
        return $this->buildResponse([
            'categoryTemplates' => [
                '<template1>' => [
                    'name' => '<name1>',
                    'categories' => [
                        '<category1>' => [
                            'title' => '<title1>',
                            'accountId' => 1,
                            'channel' => 'ebay',
                            'fieldValues' => [
                                'listingDuration' => [
                                    '<value1>' => '<value1>',
                                    '<value2>' => '<value2>',
                                    '<valueN>' => '<valueN>',
                                ],
                                'shippingMethods' => [
                                    '<value1>' => '<title1>',
                                    '<value2>' => '<title2>',
                                    '<valueN>' => '<titleN>',
                                ],
                                'itemSpecifics' => [
                                    'required' => [
                                        '<fieldName1>' => [
                                            'type' => 'select',
                                            'options' => ['<key1>' => '<value1>', '<key2>' => '<value2>', '<keyN>' => '<valueN>'],
                                            'minValues' => 1,
                                            'maxValues' => 2,
                                        ],
                                        '<fieldNameN>' => [
                                            'type' => 'select',
                                            'options' => ['<key1>' => '<value1>', '<key2>' => '<value2>', '<keyN>' => '<valueN>'],
                                            'minValues' => 1,
                                            'maxValues' => 1,
                                        ],
                                    ],
                                    'optional' => [
                                        '<fieldName1>' => [
                                            'type' => 'text',
                                            'minValues' => 0,
                                        ],
                                        '<fieldNameN>' => [
                                            'type' => 'select',
                                            'options' => ['<key1>' => '<value1>', '<key2>' => '<value2>', '<keyN>' => '<valueN>'],
                                            'minValues' => 1,
                                            'maxValues' => 2,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '<category2>' => [
                            'title' => '<title2>',
                            'accountId' => 2,
                            'channel' => 'dummy',
                            'fieldValues' => [],
                        ],
                        '<category3>' => [
                            'title' => '<title3>',
                            'accountId' => 3,
                            'channel' => 'dummy',
                            'fieldValues' => [],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function categoryChildrenAction()
    {
        try {
            return $this->buildResponse([
                'categories' => $this->channelService->getCategoryChildrenForCategoryAndAccount(
                    $this->fetchAccountFromRoute(), $this->getCategoryIdFromRoute()
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
            return $this->buildResponse([
                'categories' => $this->channelService->refetchAndSaveCategories($this->fetchAccountFromRoute())
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

    protected function getCategoryIdFromRoute(): string
    {
        return (int) $this->params()->fromRoute('categoryId', -1);
    }
}
