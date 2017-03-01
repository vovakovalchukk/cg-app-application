<?php
namespace Orders\Controller;

use CG\UserPreference\Client\Service as UserPreferenceService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Order\StoredFilters\Service;
use Orders\Order\TableService\OrdersTableUserPreferences;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class StoredFiltersController extends AbstractActionController
{
    const ROUTE_SAVE = 'save';
    const ROUTE_REMOVE = 'remove';

    /** @var Service $service */
    protected $service;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var OrdersTableUserPreferences $ordersTableUserPreferences */
    protected $ordersTableUserPreferences;
    /** @var UserPreferenceService $userPreferenceService */
    protected $userPreferenceService;

    public function __construct(
        Service $service,
        JsonModelFactory $jsonModelFactory,
        OrdersTableUserPreferences $ordersTableUserPreferences,
        UserPreferenceService $userPreferenceService
    ) {
        $this->service = $service;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->ordersTableUserPreferences = $ordersTableUserPreferences;
        $this->userPreferenceService = $userPreferenceService;
    }

    /**
     * @param $variables
     * @param $options
     * @return JsonModel
     */
    protected function newJsonModel($variables = null, $options = null)
    {
        return $this->jsonModelFactory->newInstance($variables, $options);
    }

    public function saveFilterAction()
    {
        return $this->doFilterAction('saved');
    }

    public function removeFilterAction()
    {
        return $this->doFilterAction('removed');
    }

    protected function doFilterAction($action)
    {
        $jsonModel = $this->newJsonModel([$action => false]);

        $name = trim($this->params()->fromPost('name', ''));
        if (empty($name)) {
            return $jsonModel->setVariable('error', 'Invalid Filter');
        }

        $userPreference = $this->ordersTableUserPreferences->getUserPreference();
        if ($action == 'removed') {
            $this->service->removeStoredFilter($userPreference, $name);
        } else {
            $filter = json_decode($this->params()->fromPost('filter', []), true);
            $this->service->addStoredFilter($userPreference, $name, $filter);
        }
        $this->userPreferenceService->save($userPreference);

        return $jsonModel->setVariable($action, true);
    }
} 
