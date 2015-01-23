<?php
namespace Settings\Controller;

use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\I18n\Translator\Translator;
use CG\Zend\Stdlib\Mvc\Controller\ExceptionToViewModelUserExceptionTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Settings\PickList\Service as PickListService;
use CG\Settings\PickList\Mapper as PickListMapper;
use CG\Settings\PickList\Entity as PickList;
use CG\Settings\PickList\SortValidator;

class PickListController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;
    use ExceptionToViewModelUserExceptionTrait;

    protected $activeUserContainer;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $translator;
    protected $pickListService;
    protected $pickListMapper;

    const LOG_CODE = 'PickListController';

    const ROUTE = 'Picking Management';
    const ROUTE_PICK_LIST = 'Pick List';
    const ROUTE_PICK_LIST_SAVE = 'Pick List Save';

    public function __construct(
        ActiveUserContainer $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        Translator $translator,
        PickListService $pickListService,
        PickListMapper $pickListMapper
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setTranslator($translator)
            ->setPickListService($pickListService)
            ->setPickListMapper($pickListMapper);
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE.'/' . static::ROUTE_PICK_LIST);
    }

    public function pickListAction()
    {
        $pickListSettings = $this->getPickListSettings();
        $view = $this->getViewModelFactory()->newInstance();

        $view->setTemplate('settings/picking/list');
        $view->setVariable('title', 'Pick List');
        $view->setVariable('eTag', $pickListSettings->getETag());

        $view->addChild(
            $this->getSortFieldCustomSelect(SortValidator::getSortFieldsNames(), $pickListSettings->getSortField()),
            'sortFieldCustomSelect'
        );

        $view->addChild(
            $this->getSortDirectionCustomSelect(SortValidator::getSortDirectionsNames(), $pickListSettings->getSortDirection()),
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
        $pickList = $this->savePickListSettings($pickListSettings);
        return $this->getJsonModelFactory()->newInstance(['eTag' => $pickList->getETag()]);
    }

    /**
     * @return PickList
     */
    protected function getPickListSettings()
    {
        $organisationUnitId = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        return $this->getPickListService()->fetch($organisationUnitId);
    }

    /**
     * @return PickList
     */
    protected function savePickListSettings(array $pickListSettings)
    {
        $pickListSettings['showPictures'] = $pickListSettings['showPictures'] == 'true';
        $pickListSettings['showSkuless'] = $pickListSettings['showSkuless'] == 'true';
        $pickListSettings['id'] = $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId();
        $pickList = $this->getPickListMapper()->fromArray($pickListSettings);
        $pickList->setStoredETag($pickListSettings['eTag']);
        $this->getPickListService()->save($pickList);
        return $pickList;
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
     * @return PickListMapper
     */
    protected function getPickListMapper()
    {
        return $this->pickListMapper;
    }

    /**
     * @param PickListMapper $pickListMapper
     * @return $this
     */
    public function setPickListMapper(PickListMapper $pickListMapper)
    {
        $this->pickListMapper = $pickListMapper;
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
