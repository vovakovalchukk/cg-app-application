<?php
namespace Settings\Controller;

use CG\Channel\ShippingServiceFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Zend\Stdlib\View\Model\Exception;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Di\Exception\ClassNotFoundException;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Order\Shared\Shipping\Conversion\Service as ConversionService;
use CG\Settings\Shipping\Alias\Service as ShippingService;
use CG\User\ActiveUserInterface;
use CG\Settings\Shipping\Alias\Entity as AliasEntity;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Account\Client\Service as AccountService;

class ShippingController extends AbstractActionController
{
    const ROUTE = "Shipping Management";
    const ROUTE_ALIASES = "Shipping Aliases";
    const ROUTE_ALIASES_SAVE = 'Shipping Alias Save';
    const ROUTE_ALIASES_REMOVE = 'Shipping Alias Remove';
    const ROUTE_SERVICES = 'Shipping Services';
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

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ConversionService $conversionService,
        ShippingService $shippingService,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUser,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setConversionService($conversionService)
            ->setShippingService($shippingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUser($activeUser)
            ->setOrganisationUnitService($organisationUnitService)
            ->setAccountService($accountService)
            ->setShippingServiceFactory($shippingServiceFactory);
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
        $view->setVariable('shippingAccounts', is_array($this->getShippingAccounts()) ? [] : $this->getShippingAccounts()->toArray());
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
            $aliases = $this->getShippingService()->fetchCollectionByPagination(
                static::LIMIT,
                static::FIRST_PAGE,
                [],
                [$this->getActiveUser()->getActiveUserRootOrganisationUnitId()]
            );
            $view = $this->getViewModelFactory()->newInstance();
            $view->setTemplate('settings/shipping/aliases/many');
            $aliasViews = [];
            foreach ($aliases as $alias) {
                $aliasViews[] = $this->getIndividualAliasView($alias);
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

    protected function getNoAliasesView()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('settings/shipping/aliases/none');
        return $view;
    }

    protected function getIndividualAliasView(AliasEntity $alias)
    {
        $view = $this->getViewModelFactory()->newInstance([
            'id' => 'shipping-alias-' . $alias->getId(),
            'aliasId' => $alias->getId(),
            'aliasEtag' => $alias->getETag()
        ]);
        $view->addChild($this->getTextView($alias), 'text');
        $view->addChild($this->getDeleteButtonView($alias), 'deleteButton');
        $view->addChild($this->getMultiSelectExpandedView($alias), 'multiSelectExpanded');
        $view->addChild($this->getAccountCustomSelectView($alias), 'accountCustomSelect');
        $serviceCustomSelect = $this->getServiceCustomSelectView($alias);
        if(!is_null($serviceCustomSelect)) {
            $view->addChild($this->getServiceCustomSelectView($alias), 'serviceCustomSelect');
        }
        $view->setTemplate('ShippingAlias/alias.mustache');

        return $view;
    }

    protected function getAccountCustomSelectView(AliasEntity $alias)
    {
        $shippingAccounts = $this->getShippingAccounts();

        $options = [
            [
                'title' => 'None',
                'value' => '0',
                'selected' => (!$alias->getAccountId())
            ]
        ];
        foreach($shippingAccounts as $account) {
            $options[] = [
                'title' => $account->getDisplayName(),
                'value' => $account->getId(),
                'selected' => $alias->getAccountId() == $account->getId()
            ];
        }

        $customSelect = $this->getViewModelFactory()->newInstance([
            'name' => 'shipping-account-custom-select-' . $alias->getId(),
            'id' => 'shipping-account-custom-select-' . $alias->getId(),
            'class' => 'shipping-account-select',
            'options' => $options
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');
        return $customSelect;
    }

    protected function getServiceCustomSelectView(AliasEntity $alias)
    {
        $shippingServices = $this->getShippingServices($alias->getAccountId());
        $options = [];
        foreach($shippingServices as $serviceKey => $serviceVal) {
            $options[] = [
                'title' => $serviceKey,
                'value' => $serviceVal,
                'selected' => $alias->getShippingService() == $serviceVal
            ];
        }

        if(count($options) == 0) {
            return null;
        }

        $customSelect = $this->getViewModelFactory()->newInstance([
            'name' => 'shipping-service-custom-select-' . $alias->getId(),
            'id' => 'shipping-service-custom-select-' . $alias->getId(),
            'class' => 'shipping-service-select',
            'options' => $options
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');
        return $customSelect;
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
            $shippingAccounts = $this->getAccountService()->fetchByOUAndStatus(
                $this->getActiveUser()->getActiveUser()->getOuList(),
                null,
                false,
                static::LIMIT,
                static::FIRST_PAGE,
                static::TYPE
            );
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
}
