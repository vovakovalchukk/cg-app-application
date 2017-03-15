<?php
namespace Settings\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Settings\Order\AutoArchiveTimeframe;
use CG\Settings\Order\Service as OrderSettingsService;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class OrderController extends AbstractActionController
{
    const ROUTE = 'Orders';
    const ROUTE_SAVE = 'Save';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ActiveUserContainer */
    protected $activeUserContainer;
    /** @var OrderSettingsService */
    protected $orderSettingsService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ActiveUserContainer $activeUserContainer,
        OrderSettingsService $orderSettingsService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderSettingsService($orderSettingsService);
    }


    public function indexAction()
    {
        $settings = $this->getOrderSettings();

        $view = $this->viewModelFactory->newInstance([
            'title' => 'Order Management',
            'eTag' => $settings->getStoredETag(),
        ]);

        $view->addChild($this->getAutoArchiveTimeframeSelect($settings->getAutoArchiveTimeframe()), 'autoArchiveTimeframeSelect');
        $view->addChild($this->getDispatchOrderWarningCheckbox($settings->getDispatchOrderWarning()), 'dispatchOrderWarningCheckbox');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);

        return $view;
    }

    protected function getDispatchOrderWarningCheckbox($selected)
    {
        $checkbox = $this->viewModelFactory->newInstance([
            'id' => 'dispatch-order-warning-checkbox',
            'selected' => $selected
        ]);
        $checkbox->setTemplate('elements/checkbox.mustache');
        return $checkbox;
    }

    protected function getOrderSettings()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        return $this->orderSettingsService->fetch($rootOuId);
    }

    protected function getAutoArchiveTimeframeSelect($selected = null)
    {
        $options = [];
        foreach (AutoArchiveTimeframe::getAllTimeframes() as $timeframe) {
            $options[] = [
                'value' => $timeframe,
                'title' => $timeframe,
                'selected' => ($selected == $timeframe),
            ];
        }

        $customSelect = $this->viewModelFactory->newInstance([
            'name' => 'autoArchiveTimeframe',
            'id' => 'autoArchiveTimeframe-custom-select',
            'options' => $options
        ]);
        $customSelect->setTemplate('elements/custom-select.mustache');
        return $customSelect;
    }

    public function saveAction()
    {
        $eTag = $this->params()->fromPost('eTag');
        $autoArchiveTimeframe = $this->params()->fromPost('autoArchiveTimeframe');
        $dispatchOrderWarning = $this->params()->fromPost('dispatchOrderWarning');

        $settings = $this->getOrderSettings();
        $settings->setAutoArchiveTimeframe($autoArchiveTimeframe)
            ->setDispatchOrderWarning($dispatchOrderWarning)
            ->setStoredETag($eTag);

        try {
            $settings = $this->orderSettingsService->save($settings);
        } catch (NotModified $e) {
            // No-op
        }

        return $this->jsonModelFactory->newInstance([
            'eTag' => $settings->getStoredETag()
        ]);
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setOrderSettingsService(OrderSettingsService $orderSettingsService)
    {
        $this->orderSettingsService = $orderSettingsService;
        return $this;
    }
}