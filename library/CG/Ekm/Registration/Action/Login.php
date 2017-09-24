<?php
namespace CG\Ekm\Registration\Action;

use CG\Ekm\Account\Service as EkmAccountService;
use CG\Ekm\Registration\Service as RegistrationService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\LoginException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\SetupIncomplete;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Ekm\Registration\Exception\Runtime\RegistrationNotProcessed;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\User\Service as UserService;
use CG_Login\Service\LoginService;
use CG\Stdlib\Exception\Runtime\NotAuthorisedException;

class Login implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'RegistrationLoginAction';
    const LOG_CODE_NOT_FOUND = 'NotFound';
    const LOG_MSG_REGISTRATION_NOT_FOUND = 'Failed to find registration. EKM Username: %s';

    /** @var  RegistrationService $registrationService */
    protected $registrationService;
    /** @var  EkmAccountService $ekmAccountService */
    protected $ekmAccountService;
    /** @var  UserService $userService */
    protected $userService;
    /** @var  LoginService $loginService */
    protected $loginService;
    /** @var ActiveUserContainer $activeUserContainer */
    protected $activeUserContainer;
    /** @var  OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        RegistrationService $registrationService,
        EkmAccountService $ekmAccountService,
        UserService $userService,
        LoginService $loginService,
        ActiveUserContainer $activeUserContainer,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->registrationService = $registrationService;
        $this->ekmAccountService = $ekmAccountService;
        $this->userService = $userService;
        $this->loginService = $loginService;
        $this->activeUserContainer = $activeUserContainer;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function __invoke(string $ekmUsername, string $token): void
    {
        try {
            $this->redirectActiveUser();
        } catch(LoginException $e) {
            // No-op: Continue to attempt login
        }

        try {
            $this->verifyRegistration($ekmUsername, $token);
        } catch(NotFound $e) {
            throw $e;
        } catch(RegistrationNotProcessed $e) {
            $this->redirectUserToPendingPage();
        }

        try {
            $this->loginUser($ekmUsername, $token);
        } catch(LoginException $e) {
            $this->redirectUserToSupportPage();
        }

        return;
    }

    protected function verifyRegistration(string $ekmUsername, $token): void
    {
        try {
            $registration = $this->registrationService->fetchByEkmUsernameAndToken($ekmUsername, $token);
        } catch(NotFound $e) {
            $this->logErrorException($e, static::LOG_MSG_REGISTRATION_NOT_FOUND, ['ekmUsername' => $ekmUsername], [static::LOG_CODE, static::LOG_CODE_NOT_FOUND]);
            throw $e;
        }
        if (!$registration->getRootOrganisationUnitId()) {
            throw new RegistrationNotProcessed('Registration not processed for EKM username: '.$ekmUsername);
        }
        $registrationJson = $registration->getJson(true);
    }

    protected function loginUser(string $ekmUsername, $token): void
    {
        $ekmAccount = $this->ekmAccountService->fetchByEkmUsername($ekmUsername);
        $rootOrganisationUnit = $this->organisationUnitService->getRootOuFromOuId(
            $ekmAccount->getOrganisationUnitId()
        );
        try {
            $user = $this->userService->fetchByUsername($ekmUsername);
        } catch(NotFound $e) {
            // Log error exception
            throw new LoginException('Failed to fetch user with username: '.$ekmUsername);
        }
        try {
            $this->loginService->loginUser($user);
        } catch(NotAuthorisedException $e) {
            // Log error exception
            throw new LoginException('Failed to authorize user with username: '.$ekmUsername);
        }

        $this->redirectUserOnCompletion($rootOrganisationUnit);
        return;
    }

    protected function redirectActiveUser(): void
    {
        if (!$user = $this->activeUserContainer->getActiveUser()) {
            throw new LoginException('User is not logged in');
        }
        try {
            $rootOrganisationUnit = $this->organisationUnitService->fetch(
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
            );
        } catch(NotFound $e) {
            throw new LoginException('Failed to find root organisation unit for active user');
        }
        return $this->redirectUserOnCompletion($rootOrganisationUnit);
    }

    protected function redirectUserOnCompletion(OrganisationUnit $rootOrganisationUnit): void
    {
        try {
            $this->checkSetupWizardCompleted($rootOrganisationUnit);
        } catch(SetupIncomplete $e) {
            $this->redirectUserToSetupChannelPickPage();
        }
        $this->redirectUserToLoginPage();
        return;
    }

    protected function checkSetupWizardCompleted(OrganisationUnit $rootOrganisationUnit): void
    {
        if (!$rootOrganisationUnit->getMetaData()->toArray()['setupCompleteDate']) {
            throw new SetupWizardIncomplete('User has not completed the setup wizard');
        }
        return;
    }

    protected function redirectUserToSetupChannelPickPage(): void
    {
        $setupChannelPickUrl = $this->registrationService->getSetupChannelPickUrl();
    }

    protected function redirectUserToLoginPage(): void
    {
        $loginUrl = $this->registrationService->getLoginUrl();
    }

    protected function redirectUserToPendingPage(): void
    {
        $pendingUrl = $this->registrationService->getPendingUrl();
    }

    protected function redirectUserToSupportPage(): void
    {
        $supportUrl = $this->registrationService->getSupportUrl();
    }
}
