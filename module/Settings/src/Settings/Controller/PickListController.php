<?php
namespace Settings\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\Zend\Stdlib\Mvc\Controller\ExceptionToViewModelUserExceptionTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Module;
use Settings\PickList\Service as PickListService;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Settings\PickList\SortValidator;

class PickListController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;
    use ExceptionToViewModelUserExceptionTrait;

    /** @var ActiveUserContainer */
    protected $activeUserContainer;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Translator */
    protected $translator;
    /** @var PickListService */
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
        $this->activeUserContainer = $activeUserContainer;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->viewModelFactory = $viewModelFactory;
        $this->translator = $translator;
        $this->pickListService = $pickListService;
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE.'/' . static::ROUTE_PICK_LIST);
    }

    public function pickListAction()
    {
        $pickListSettings = $this->pickListService->getPickListSettings($this->getOrganisationUnitId());
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('settings/picking/list');
        $view->setVariable('title', 'Pick List');
        $view->setVariable('eTag', $pickListSettings->getStoredETag());
        $view->setVariable('pickList', $pickListSettings->toArray());
        $view->setVariable('sortFields', $this->pickListService->getSortFields());
        $view->setVariable('sortFieldsMap', [
            SortValidator::SORT_FIELD_PICKING_LOCATION => 'showPickingLocations',
        ]);
        $view->setVariable('sortDirections', $this->pickListService->getSortDirections());
        $view->setVariable('saveRoute', $this->url()->fromRoute(implode('/', [
            Module::ROUTE,
            PickListController::ROUTE,
            PickListController::ROUTE_PICK_LIST,
            PickListController::ROUTE_PICK_LIST_SAVE,
        ])));
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }

    public function saveAction()
    {
        $pickListSettings = $this->params()->fromPost();
        $pickList = $this->pickListService->savePickListSettings($pickListSettings, $this->getOrganisationUnitId());
        return $this->jsonModelFactory->newInstance(['eTag' => $pickList->getStoredETag()]);
    }

    protected function getOrganisationUnitId()
    {
        return $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
    }

    /** Required by trait */
    protected function getJsonModelFactory(): JsonModelFactory
    {
        return $this->jsonModelFactory;
    }

    /** Required by trait */
    protected function getTranslator(): Translator
    {
        return $this->translator;
    }
}
