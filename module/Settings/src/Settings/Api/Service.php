<?php
namespace Settings\Api;

use CG\ApiCredentials\Entity as ApiCredentials;
use CG\ApiCredentials\Service as ApiCredentialsService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_UI\View\Prototyper\ViewModelFactory;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'ApiSettingsService';
    const LOG_CREDENTIALS_GEN = 'Public API credentials not found for OU %d, will generate';

    /** @var ActiveUserContainer */
    protected $activeUserContainer;
    /** @var ApiCredentialsService */
    protected $apiCredentialsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        ActiveUserContainer $activeUserContainer,
        ViewModelFactory $viewModelFactory,
        ApiCredentialsService $apiCredentialsService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->viewModelFactory = $viewModelFactory;
        $this->apiCredentialsService = $apiCredentialsService;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function getCredentialsForActiveUser(): ApiCredentials
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        try {
            return $this->apiCredentialsService->fetch($rootOuId);
        } catch (NotFound $ex) {
            $this->logDebug(static::LOG_CREDENTIALS_GEN, [$rootOuId], static::LOG_CODE);
            return $this->generateCredentialsForActiveUser();
        }
    }

    protected function generateCredentialsForActiveUser(): ApiCredentials
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $rootOu = $this->organisationUnitService->fetch($rootOuId);
        return $this->apiCredentialsService->generateForOu($rootOu);
    }
}