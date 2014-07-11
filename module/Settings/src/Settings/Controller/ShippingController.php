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
use Mustache\View\Renderer;

class ShippingController extends AbstractActionController
{
    const ROUTE = "Shipping Management";
    const ROUTE_ALIASES = "Shipping Aliases";
    const ROUTE_ALIASES_SAVE = 'Shipping Alias Save';
    const FIRST_PAGE = 1;
    const LIMIT = 'all';

    protected $viewModelFactory;
    protected $conversionService;
    protected $shippingService;
    protected $jsonModelFactory;
    protected $activeUser;
    protected $renderer;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ConversionService $conversionService,
        ShippingService $shippingService,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUser,
        Renderer $renderer
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setConversionService($conversionService)
            ->setShippingService($shippingService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUser($activeUser)
            ->setRenderer($renderer);
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
            $count = 0;
            $aliasViews = [];
            foreach ($aliases as $alias) {
                $aliasViews[] = $this->getIndividualAliasView($alias, $count);
                $count++;
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

    protected function getIndividualAliasView(AliasEntity $alias, $id)
    {
        $view = $this->getViewModelFactory()->newInstance([
            'id' => 'shipping-alias-' . $id
        ]);
        $view->addChild($this->getTextView($alias, $id), 'text');
        $view->addChild($this->getDeleteButtonView($id), 'deleteButton');
        $view->addChild($this->getMultiSelectExpandedView($alias, $id), 'multiSelectExpanded');
        $view->setTemplate('ShippingAlias/alias.mustache');

        return $view;
    }

    protected function getDeleteButtonView($id)
    {
        $button = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => $this->translate("Delete"),
            'id' => "deleteButton-" . $id
        ]);
        $button->setTemplate('elements/buttons.mustache');
        return $button;
    }

    protected function getTextView(AliasEntity $alias, $id)
    {
        $text = $this->getViewModelFactory()->newInstance([
            'name' => "alias-name-" . $id,
            'value' => $alias->getName()
        ]);
        $text->setTemplate('elements/text.mustache');
        return $text;
    }

    protected function getMultiSelectExpandedView(AliasEntity $alias, $id)
    {
        $shippingMethods = $this->getConversionService()->fetchMethods();
        $options = [];
        foreach ($shippingMethods as $shippingMethod) {
            $options[] = [
                "title" => $shippingMethod->getMethod(),
                "value" => $shippingMethod->getId(),
                "selected" => in_array($shippingMethod->getId(), $alias->getMethods()->getIds())
            ];
        }
        $multiSelect = $this->getViewModelFactory()->newInstance([
            'name' => "aliasMultiSelect-" . $id,
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

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
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

    protected function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    protected function getRenderer()
    {
        return $this->renderer;
    }
}