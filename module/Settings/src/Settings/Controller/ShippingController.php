<?php
namespace Settings\Controller;

use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Zend\Stdlib\View\Model\Exception;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Di\Exception\ClassNotFoundException;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Order\Shared\Shipping\Conversion\Service as ConversionService;
use CG\Settings\Shipping\Alias\Service as ShippingService;
use CG\User\ActiveUserInterface;
use CG\Settings\Shipping\Alias\Collection as AliasCollection;
use CG\Settings\Shipping\Alias\Entity as AliasEntity;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Account\Shared\Collection as AccountCollection;
use Orders\Courier\ShippingAccountsService;

class ShippingController extends AbstractActionController
{
    const ROUTE = "Shipping Management";
    const ROUTE_ALIASES = "Shipping Aliases";
    const ROUTE_ALIASES_SAVE = 'Shipping Alias Save';
    const ROUTE_ALIASES_REMOVE = 'Shipping Alias Remove';
    const ROUTE_SERVICES = 'Shipping Services';
    const ROUTE_SERVICE_OPTIONS = 'Shipping Service Options';
    const FIRST_PAGE = 1;
    const LIMIT = 'all';
    const TYPE = 'shipping';

    protected $viewModelFactory;
    protected $conversionService;
    protected $shippingService;
    protected $jsonModelFactory;
    protected $activeUser;
    protected $organisationUnitService;
    protected $accountService;
    protected $shippingServiceFactory;
    /** @var ShippingAccountsService */
    protected $shippingAccountsService;

    protected $serviceOptionsTypeMap = [
        'select' => 'custom-select',
        'multiselect' => 'custom-select-group',
    ];

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ConversionService $conversionService,
        ShippingService $shippingService,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUser,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        ShippingAccountsService $shippingAccountsService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setConversionService($conversionService)
            ->setShippingService($shippingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUser($activeUser)
            ->setOrganisationUnitService($organisationUnitService)
            ->setAccountService($accountService)
            ->setShippingServiceFactory($shippingServiceFactory)
            ->setShippingAccountsService($shippingAccountsService);
    }

    public function aliasAction()
    {
        $organisationUnit = $this->getOrganisationUnitService()
                                ->fetch(
                                    $this->getActiveUser()
                                        ->getActiveUserRootOrganisationUnitId()
                                );

        $shippingMethods = $this->getConversionService()->fetchMethods($organisationUnit);
        $view = $this->getViewModelFactory()->newInstance();
        $view->setVariable('title', static::ROUTE_ALIASES);
        $view->setVariable('shippingMethods', $shippingMethods->toArray());
        $view->setVariable('shippingAccountOptions', $this->getShippingAccountOptions($this->getShippingAccounts()));
        $view->setVariable('rootOuId', $this->getActiveUser()->getActiveUserRootOrganisationUnitId());
        $view->addChild($this->getAliasView(), 'aliases');
        $view->addChild($this->getAddButtonView(), 'addButton');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }

    public function aliasSaveAction()
    {
        $alias = $this->getShippingService()->saveFromJson($this->params()->fromPost('alias'));
        return $this->getJsonModelFactory()->newInstance(["alias" => json_encode($alias)]);
    }

    public function aliasDeleteAction()
    {
        $alias = $this->params()->fromPost('alias');
        $decodedAlias = json_decode($alias, true);
        $this->getShippingService()->removeById($decodedAlias['id']);
        return $this->getJsonModelFactory()->newInstance(["alias" => $alias]);
    }

    public function getServicesAction()
    {
        $accountId = $this->params('account');
        return $this->getJsonModelFactory()->newInstance(['shippingServices' => $this->getShippingServices($accountId)]);
    }

    public function getServiceOptionsAction()
    {
        $accountId = $this->params()->fromRoute('account');
        $service = $this->params()->fromPost('service');
        return $this->getJsonModelFactory()->newInstance(['shippingServiceOptions' => $this->getShippingServiceOptions($accountId, $service)]);
    }

    protected function getAddButtonView()
    {
        $button = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->translate("Add"),
            'id' => "addButtonSelector"
        ]);
        $button->setTemplate('elements/buttons.mustache');
        return $button;
    }

    protected function getAliasView()
    {
        try {
            $aliases = $this->fetchAliasesForActiveUser();
            $aliasAccounts = $this->fetchAccountsForAliases($aliases);
            $view = $this->getViewModelFactory()->newInstance();
            $view->setTemplate('settings/shipping/aliases/many');
            $aliasViews = [];
            foreach ($aliases as $alias) {
                $account = null;
                if ($alias->getAccountId()) {
                    $account = ($aliasAccounts->getById($alias->getAccountId()));
                }
                $aliasViews[] = $this->getIndividualAliasView($alias, $account);
            }
            $aliasViews = array_reverse($aliasViews);
            foreach ($aliasViews as $aliasView) {
                $view->addChild($aliasView, 'aliases', true);
            }
            return $view;
        } catch (NotFound $e) {
            return $this->getNoAliasesView();
        }
    }

    protected function fetchAliasesForActiveUser()
    {
        return $this->getShippingService()->fetchCollectionByPagination(
            static::LIMIT,
            static::FIRST_PAGE,
            [],
            [$this->getActiveUser()->getActiveUserRootOrganisationUnitId()]
        );
    }

    protected function fetchAccountsForAliases(AliasCollection $aliases)
    {
        $accountIds = array_filter($aliases->getArrayOf('accountId'));
        if (empty($accountIds)) {
            return [];
        }
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($accountIds);
        return $this->accountService->fetchByFilter($filter);
    }

    protected function getNoAliasesView()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('settings/shipping/aliases/none');
        return $view;
    }

    protected function getIndividualAliasView(AliasEntity $alias, AccountEntity $account = null)
    {
        $shippingAccounts = $this->getShippingAccounts();

        $view = $this->getViewModelFactory()->newInstance([
            'id' => 'shipping-alias-' . $alias->getId(),
            'aliasId' => $alias->getId(),
            'aliasEtag' => $alias->getStoredETag(),
            'hasAccounts' => count($shippingAccounts),
        ]);
        $view->setTemplate('ShippingAlias/alias.mustache');

        $view->addChild($this->getTextView($alias), 'text');
        $view->addChild($this->getDeleteButtonView($alias), 'deleteButton');
        $view->addChild($this->getMultiSelectExpandedView($alias), 'multiSelectExpanded');

        if (count($shippingAccounts)) {
            $view->addChild($this->getAccountCustomSelectView($alias, $shippingAccounts), 'accountCustomSelect');
            $serviceCustomSelect = $this->getServiceCustomSelectView($alias);
            if (is_null($serviceCustomSelect)) {
                return $view;
            }
            $view->addChild($serviceCustomSelect, 'serviceCustomSelect');
            $serviceOptions = $this->getServiceOptionsView($alias, $account);
            if ($serviceOptions) {
                $view->addChild($serviceOptions, 'serviceOptions');
            }
        }

        return $view;
    }

    protected function getAccountCustomSelectView(AliasEntity $alias, $shippingAccounts)
    {
        $options = $this->getShippingAccountOptions($shippingAccounts, $alias);
        
        $customSelect = $this->getViewModelFactory()->newInstance([
            'name' => 'shipping-account-custom-select-' . $alias->getId(),
            'id' => 'shipping-account-custom-select-' . $alias->getId(),
            'class' => 'shipping-account-select',
            'marginClass' => 'u-margin-top-small',
            'options' => $options,
            'content-type' => 'open-content u-max-width-initial u-width-100pc'
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');
        return $customSelect;
    }

    protected function getShippingAccountOptions($shippingAccounts, $alias = null)
    {
        $options = [];
        if (! $shippingAccounts instanceof AccountCollection) {
            return $options;
        }
        $noneOption = [
            'title' => 'None',
            'value' => '0',
            'selected' => ($alias ? !$alias->getAccountId() : 'false')
        ];
        $options = $this->shippingAccountsService->convertShippingAccountsToOptions(
            $shippingAccounts, ($alias ? $alias->getAccountId() : null)
        );
        array_unshift($options, $noneOption);
        return $options;
    }

    protected function getServiceCustomSelectView(AliasEntity $alias)
    {
        $shippingServices = $this->getShippingServices($alias->getAccountId());

        $options = [];
        foreach($shippingServices as $serviceKey => $serviceVal) {
            $options[] = [
                'title' => $serviceVal,
                'value' => $serviceKey,
                'selected' => $alias->getShippingService() == $serviceKey
            ];
        }

        if(count($options) == 0) {
            return null;
        }

        if (count($options) == 1) {
            $index = key($options);
            $options[$index]['selected'] = true;
        }

        $title = array_column($options, 'title');
        array_multisort($title, SORT_ASC, $options);

        $customSelect = $this->getViewModelFactory()->newInstance([
            'name' => 'shipping-service-custom-select-' . $alias->getId(),
            'id' => 'shipping-service-custom-select-' . $alias->getId(),
            'class' => 'shipping-service-select',
            'marginClass' => 'u-margin-top-small',
            'sizeClass' => 'u-width-100pc',
            'searchField' => true,
            'options' => $options,
            'content-type' => 'open-content u-max-width-initial u-width-100pc'
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');

        return $customSelect;
    }

    protected function getServiceOptionsView(AliasEntity $alias, AccountEntity $account = null)
    {
        if (!$account || !$alias->getShippingService()) {
            return null;
        }
        $shippingService = $this->shippingServiceFactory->createShippingService($account);
        if (!$shippingService->doesServiceHaveOptions($alias->getShippingService())) {
            return null;
        }
        $optionsData = $shippingService->getOptionsForService($alias->getShippingService(), $alias->getOptions());
        $type = $optionsData['inputType'];
        if (isset($this->serviceOptionsTypeMap[$type])) {
            $type = $this->serviceOptionsTypeMap[$type];
        }
        $viewData = [
            'name' => 'shipping-service-options-' . $alias->getId(),
            'id' => 'shipping-service-options-' . $alias->getId(),
            'class' => 'shipping-service-options-input',
        ];
        $viewData = array_merge($viewData, $optionsData);
        $view = $this->getViewModelFactory()->newInstance($viewData);
        $view->setTemplate('elements/' . $type . '.mustache');
        return $view;
    }

    protected function getDeleteButtonView(AliasEntity $alias)
    {
        $button = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->translate("Delete"),
            'id' => "deleteButton-" . $alias->getId()
        ]);
        $button->setTemplate('elements/buttons.mustache');
        return $button;
    }

    protected function getTextView(AliasEntity $alias)
    {
        $text = $this->getViewModelFactory()->newInstance([
            'name' => "alias-name-" . $alias->getId(),
            'value' => $alias->getName()
        ]);
        $text->setTemplate('elements/text.mustache');
        return $text;
    }

    protected function getMultiSelectExpandedView(AliasEntity $alias)
    {
        $organisationUnit = $this->getOrganisationUnitService()
                                 ->fetch($this->getActiveUser()
                                              ->getActiveUserRootOrganisationUnitId()
            );
        $shippingMethods = $this->getConversionService()->fetchMethods($organisationUnit);
        $options = [];
        $methodIds = array_flip($alias->getMethods()->getIds());
        foreach ($shippingMethods as $shippingMethod) {
            $options[] = [
                "title" => $shippingMethod->getMethod(),
                "value" => $shippingMethod->getId(),
                "selected" => isset($methodIds[$shippingMethod->getId()])
            ];
        }
        $multiSelect = $this->getViewModelFactory()->newInstance([
            'name' => "aliasMultiSelect-" . $alias->getId(),
            'options' => $options
        ]);
        $multiSelect->setTemplate('elements/custom-select-group.mustache');
        $multiSelectExpanded = $this->getViewModelFactory()->newInstance();
        $multiSelectExpanded->addChild($multiSelect, 'multiSelect');
        $multiSelectExpanded->setTemplate('elements/multiselectexpanded.mustache');
        return $multiSelectExpanded;
    }

    protected function getShippingAccounts()
    {
        $shippingAccounts = [];
        try {
            $shippingAccounts = $this->shippingAccountsService->getShippingAccounts();
        } catch (NotFound $e) {
            // Ignore if there are no shipping accounts
        }
        return $shippingAccounts;
    }

    protected function getShippingServices($accountId)
    {
        $shippingServices = [];
        if ((int)$accountId < 1) {
            return $shippingServices;
        }
        try {
            $account = $this->getAccountService()->fetch($accountId);
        } catch (NotFound $e) {
            return $shippingServices;
        }
        try {
            $shippingService = $this->getShippingServiceFactory()->createShippingService($account);
            $shippingServices = $shippingService->getShippingServices();
        } catch (ClassNotFoundException $e) {
            // Ignore
        }
        return $shippingServices;
    }

    protected function getShippingServiceOptions($accountId, $service)
    {
        $account = $this->getAccountService()->fetch($accountId);
        $shippingService = $this->getShippingServiceFactory()->createShippingService($account);
        return $shippingService->getOptionsForService($service);
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getConversionService()
    {
        return $this->conversionService;
    }

    protected function setConversionService(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
        return $this;
    }

    protected function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    protected function getShippingService()
    {
        return $this->shippingService;
    }

    protected function setJsonModelFactory($jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function setActiveUser(ActiveUserInterface $activeUser)
    {
        $this->activeUser = $activeUser;
        return $this;
    }

    protected function getActiveUser()
    {
        return $this->activeUser;
    }

    /**
     * @return OrganisationUnitService
     */
    protected function getOrganisationUnitService()
    {
        return $this->organisationUnitService;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function getAccountService()
    {
        return $this->accountService;
    }

    public function setShippingServiceFactory($shippingServiceFactory)
    {
        $this->shippingServiceFactory = $shippingServiceFactory;
        return $this;
    }

    protected function getShippingServiceFactory()
    {
        return $this->shippingServiceFactory;
    }

    protected function setShippingAccountsService(ShippingAccountsService $shippingAccountsService)
    {
        $this->shippingAccountsService = $shippingAccountsService;
        return $this;
    }
}
