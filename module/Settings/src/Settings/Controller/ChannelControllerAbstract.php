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
use Partner\Account\AuthoriseService as PartnerAuthoriseService;
use Zend\Mvc\Controller\AbstractActionController;

abstract class ChannelControllerAbstract extends AbstractActionController
{
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var PartnerAuthoriseService */
    protected $partnerAuthoriseService;

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService,
        PartnerAuthoriseService $partnerAuthoriseService
    ) {
        $this->accountCreationService = $accountCreationService;
        $this->activeUserContainer = $activeUserContainer;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->viewModelFactory = $viewModelFactory;
        $this->featureFlagsService = $featureFlagsService;
        $this->organisationUnitService = $organisationUnitService;
        $this->partnerAuthoriseService = $partnerAuthoriseService;
    }

    /**
     * @return AccountCreationService
     */
    protected function getAccountCreationService()
    {
        return $this->accountCreationService;
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
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }
}
