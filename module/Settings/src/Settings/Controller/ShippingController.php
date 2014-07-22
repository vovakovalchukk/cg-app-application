<?php
namespace Settings\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Order\Shared\Shipping\Conversion\Service as ConversionService;
use CG\Settings\Alias\Service as ShippingService;
use CG\User\ActiveUserInterface;
use CG\Settings\Alias\Entity as AliasEntity;

class ShippingController extends AbstractActionController
{
    const ROUTE = "Shipping Management";
    const ROUTE_ALIASES = "Shipping Aliases";
    const ROUTE_ALIASES_SAVE = 'Shipping Alias Save';
    const ROUTE_ALIASES_REMOVE = 'Shipping Alias Remove';
    const FIRST_PAGE = 1;
    const LIMIT = 'all';

    protected $viewModelFactory;
    protected $conversionService;
    protected $shippingService;
    protected $jsonModelFactory;
    protected $activeUser;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ConversionService $conversionService,
        ShippingService $shippingService,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUser
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setConversionService($conversionService)
            ->setShippingService($shippingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUser($activeUser);
    }

    public function aliasAction()
    {
        $shippingMethods = $this->getConversionService()->fetchMethods();
        $view = $this->getViewModelFactory()->newInstance();
        $view->setVariable('title', static::ROUTE_ALIASES);
        $view->setVariable('shippingMethods', $shippingMethods->toArray());
        $view->setVariable('rootOuId', $this->getActiveUser()->getActiveUserRootOrganisationUnitId());
        $view->addChild($this->getAliasView(), 'aliases');
        $view->addChild($this->getAddButtonView(), 'addButton');
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
        $view->setTemplate('ShippingAlias/alias.mustache');

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
        $shippingMethods = $this->getConversionService()->fetchMethods();
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
}