<?php
namespace Settings\Controller;

use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\I18n\Translator\Translator;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Zend\Stdlib\Mvc\Controller\ExceptionToViewModelUserExceptionTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Settings\PickList\Service as PickListService;

class PickListController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;
    use ExceptionToViewModelUserExceptionTrait;

    protected $activeUserContainer;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $translator;
    protected $pickListService;

    const LOG_CODE = 'PickListController';

    const ROUTE = 'Picking Management';
    const ROUTE_PICK_LIST = 'Pick List';
    const ROUTE_PICK_LIST_SAVE = 'Pick List Save';

    public function __construct(
        ActiveUserContainer $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        Translator $translator,
        PickListService $pickListService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setTranslator($translator)
            ->setPickListService($pickListService);
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE.'/' . static::ROUTE_PICK_LIST);
    }

    public function pickListAction()
    {
        $pickListSettings = $this->getPickListService()->getPickListSettings($this->getOrganisationUnitId());
        $view = $this->getViewModelFactory()->newInstance();

        $view->setTemplate('settings/picking/list');
        $view->setVariable('title', 'Pick List');
        $view->setVariable('eTag', $pickListSettings->getStoredETag());

        $view->addChild(
            $this->getSortFieldCustomSelect($this->getPickListService()->getSortFields(), $pickListSettings->getSortField()),
            'sortFieldCustomSelect'
        );

        $view->addChild(
            $this->getSortDirectionCustomSelect($this->getPickListService()->getSortDirections(), $pickListSettings->getSortDirection()),
            'sortDirectionCustomSelect'
        );

        $view->addChild($this->getShowPicturesCheckbox($pickListSettings->getShowPictures()), 'showPicturesCheckbox');
        $view->addChild($this->getShowSkulessCheckbox($pickListSettings->getShowSkuless()), 'showSkulessCheckbox');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }

    public function saveAction()
    {
        $pickListSettings = $this->params()->fromPost();
        $pickList = $this->getPickListService()->savePickListSettings($pickListSettings, $this->getOrganisationUnitId());
        return $this->getJsonModelFactory()->newInstance(['eTag' => $pickList->getStoredETag()]);
    }
    
    protected function getSortFieldCustomSelect(array $sortFields, $selectedSortField)
    {
        $options = [];
        foreach($sortFields as $sortField => $name) {
            $options[] = [
                'title' => $name,
                'value' => $sortField,
                'selected' => $sortField == $selectedSortField
            ];
        }

        $customSelect = $this->getViewModelFactory()->newInstance([
            'name' => 'sort-field-custom-select',
            'id' => 'sort-field-custom-select',
            'options' => $options
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');
        return $customSelect;
    }

    protected function getSortDirectionCustomSelect(array $sortDirections, $selectedSortDirection)
    {
        $options = [];
        foreach($sortDirections as $sortDirection => $name) {
            $options[] = [
                'title' => $name,
                'value' => $sortDirection,
                'selected' => $sortDirection == $selectedSortDirection
            ];
        }

        $customSelect = $this->getViewModelFactory()->newInstance([
            'name' => 'sort-direction-custom-select',
            'id' => 'sort-direction-custom-select',
            'options' => $options
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');
        return $customSelect;
    }

    protected function getShowPicturesCheckbox($selected)
    {
        $checkbox = $this->getViewModelFactory()->newInstance([
            'id' => 'show-pictures-checkbox',
            'selected' => $selected
        ]);
        $checkbox->setTemplate('elements/checkbox.mustache');
        return $checkbox;
    }

    protected function getShowSkulessCheckbox($selected)
    {
        $checkbox = $this->getViewModelFactory()->newInstance([
            'id' => 'show-skuless-checkbox',
            'selected' => $selected
        ]);
        $checkbox->setTemplate('elements/checkbox.mustache');
        return $checkbox;
    }

    protected function getOrganisationUnitId()
    {
        return $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
    }

    /**
     * @return PickListService
     */
    protected function getPickListService()
    {
        return $this->pickListService;
    }

    /**
     * @param PickListService $pickListService
     * @return $this
     */
    public function setPickListService(PickListService $pickListService)
    {
        $this->pickListService = $pickListService;
        return $this;
    }

    /**
     * @param ActiveUserContainer $activeUserContainer
     * @return $this
     */
    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserContainer
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @param JsonModelFactory $jsonModelFactory
     * @return $this
     */
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @param ViewModelFactory $viewModelFactory
     * @return $this
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param Translator $translator
     * @return $this
     */
    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }
}
