<?php
namespace Settings\Controller;

use CG\ApiCredentials\Service as ApiCredentialsService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;

class ApiController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $activeUserContainer;
    protected $viewModelFactory;
    protected $apiCredentialsService;
    protected $organisationUnitService;

    const LOG_CODE = 'ApiController';
    const LOG_CREDENTIALS_GEN = 'Public API credentials not found for OU %d, will generate';

    const ROUTE = 'Advanced';
    const ROUTE_API = 'API';

    public function __construct(
        ActiveUserContainer $activeUserContainer,
        ViewModelFactory $viewModelFactory,
        ApiCredentialsService $apiCredentialsService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->setActiveUserContainer($activeUserContainer)
            ->setViewModelFactory($viewModelFactory)
            ->setApiCredentialsService($apiCredentialsService)
            ->setOrganisationUnitService($organisationUnitService);
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE.'/' . static::ROUTE_API);
    }

    public function detailsAction()
    {
        $credentials = $this->ensureApiCredentials();

        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('credentialsKey', $credentials->getKey())
            ->setVariable('credentialsSecret', $credentials->getSecret())
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true);
        return $view;
    }

    protected function ensureApiCredentials()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        try {
            $credentials = $this->apiCredentialsService->fetch($rootOuId);
        } catch (NotFound $ex) {
            $this->logDebug(static::LOG_CREDENTIALS_GEN, [$rootOuId], static::LOG_CODE);
            $rootOu = $this->organisationUnitService->fetch($rootOuId);
            $credentials = $this->apiCredentialsService->generateForOu($rootOu);
        }
        return $credentials;
    }

    protected function setActiveUserContainer(ActiveUserContainer $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setApiCredentialsService(ApiCredentialsService $apiCredentialsService)
    {
        $this->apiCredentialsService = $apiCredentialsService;
        return $this;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}
