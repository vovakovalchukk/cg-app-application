<?php
namespace Settings\Controller;

use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

abstract class ChannelControllerAbstract extends AbstractActionController
{
    protected $accountCreationService;
    protected $activeUserContainer;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->setAccountCreationService($accountCreationService)
            ->setActiveUserContainer($activeUserContainer)
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setFeatureFlagsService($featureFlagsService)
            ->setOrganisationUnitService($organisationUnitService);
    }

    /**
     * @return self
     */
    protected function setAccountCreationService(AccountCreationService $accountCreationService)
    {
        $this->accountCreationService = $accountCreationService;
        return $this;
    }

    /**
     * @return AccountCreationService
     */
    protected function getAccountCreationService()
    {
        return $this->accountCreationService;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @return User
     */
    protected function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    protected function getActiveUserRootOrganisationUnit(): OrganisationUnit
    {
        return $this->organisationUnitService->fetch($this->activeUserContainer->getActiveUserRootOrganisationUnitId());
    }

    /**
     * @return self
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
     * @return self
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

    protected function setFeatureFlagsService(FeatureFlagsService $featureFlagsService): ChannelControllerAbstract
    {
        $this->featureFlagsService = $featureFlagsService;
        return $this;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService): ChannelControllerAbstract
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}
