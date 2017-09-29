<?php
namespace CG\Ekm\Registration\Action;

use CG\Account\Shared\Entity as Account;
use CG\Ekm\Account\Service as EkmAccountService;
use CG\Ekm\Registration\Entity as Registration;
use CG\Ekm\Registration\Exception\Runtime\RegistrationCompleteForLoggedInUser;
use CG\Ekm\Registration\Exception\Runtime\RegistrationCompleteForLoggedOutUser;
use CG\Ekm\Registration\Exception\Runtime\RegistrationFailed;
use CG\Ekm\Registration\Exception\Runtime\RegistrationPending;
use CG\Ekm\Registration\Service as RegistrationService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\LoginException;
use CG\Stdlib\Exception\Runtime\NotAuthorisedException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\SetupIncomplete;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG\User\Entity as User;
use CG\User\Service as UserService;
use CG_Login\Service\LoginService;
use Exception;

class Login implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'RegistrationLoginAction';
    const LOG_CODE_CHECK_LOGIN_STATUS = 'CheckLoggedInStatus';
    const LOG_MSG_LOGGED_OUT_USER = 'Logged out user. Token: %s';
    const LOG_CODE_REGISTRATION_NOT_FOUND = 'RegistrationNotFound';
    const LOG_MSG_REGISTRATION_NOT_FOUND = 'Failed to find registration. Token: %s';
    const LOG_CODE_REGISTRATION_STATUS = 'RegistrationStatus';
    const LOG_MSG_REGISTRATION_NOT_PROCESSED = 'Registration (%d) not processed. EKM Username: %s, Email: %s, Token: %s';
    const LOG_CODE_REGISTRATION_ATTEMPT = 'RegistrationAttempt';
    const LOG_MSG_REGISTRATION_RECREATE_JOB = 'Recreating registration (%d) Gearman job. EKM Username: %s, Email: %s, Token: %s';
    const LOG_CODE_AUTO_LOGIN = 'AutoLogin';
    const LOG_MSG_AUTO_LOGIN_ERROR = 'Failed to login user for registration (%d), an error occurred. EKM Account: %d, EKM Username: %s, Email: %s, Token: %s';

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

    public function __invoke(string $token): Registration
    {
        // Check user logged in
        try {
            $user = $this->checkUserLoggedIn();
        } catch(LoginException $e) {
            $this->logDebug(static::LOG_MSG_LOGGED_OUT_USER, ['token' => $token], [static::LOG_CODE, static::LOG_CODE_CHECK_LOGIN_STATUS]);
            // No-op
        }

        // Fetch registration
        try {
            $registration = $this->registrationService->fetchByToken($token);
        } catch(NotFound $e) {
            $this->logErrorException($e, static::LOG_MSG_REGISTRATION_NOT_FOUND, ['token' => $token], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_NOT_FOUND]);
            throw new RegistrationFailed(static::LOG_CODE_REGISTRATION_NOT_FOUND);
        }

        // Fetch EKM account (checks registration complete: we don't need to check the root organisation unit id on the registration entity)
        try {
            $ekmAccount = $this->fetchEkmAccount($registration->getEkmUsername());
        } catch(NotFound $e) {
            $this->logDebug(static::LOG_MSG_REGISTRATION_NOT_PROCESSED, ['registration' => $registration->getId(), 'ekmUsername' => $registration->getEkmUsername(), 'email' => $registration->getEmailAddress(), 'token' => $token], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_STATUS]);
            $this->recreateEkmRegistrationJob($registration);
            throw new RegistrationPending(static::LOG_CODE_REGISTRATION_STATUS);
        }

        // Check if Setup Wizard complete
        try {
            $this->checkSetupWizardCompleted($registration->getOrganisationUnit());
            if (isset($user)) {
                throw new RegistrationCompleteForLoggedInUser(static::LOG_CODE_REGISTRATION_STATUS);
            }
            throw new RegistrationCompleteForLoggedOutUser(static::LOG_CODE_REGISTRATION_STATUS);
        } catch(SetupIncomplete $e) {
            // No-op:
            // If logged-in: Setup Wizard has taken over.
            // If not logged-in: Auto-login and redirect to Setup Wizard on the Channel Pick page
        }

        // Auto-login user
        try {
            $this->loginUser($registration->getEkmUsername());
        } catch(Exception $e) {
            $this->logErrorException($e, static::LOG_MSG_AUTO_LOGIN_ERROR, ['registration' => $registration->getId(), 'ekmAccount' => $ekmAccount->getId(), 'ekmUsername' => $ekmAccount->getEkmUsername(), 'email' => $registration->getEmailAddress(), 'token' => $registration->getToken()], [static::LOG_CODE, static::LOG_CODE_AUTO_LOGIN]);
            throw new RegistrationFailed(static::LOG_CODE_AUTO_LOGIN);
        }

        return $registration;
    }

    protected function checkUserLoggedIn(): User
    {
        if (!$user = $this->activeUserContainer->getActiveUser()) {
            throw new LoginException('User is not logged in');
        }
        try {
            $this->organisationUnitService->fetch(
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
            );
            return $user;
        } catch(NotFound $e) {
            throw new LoginException('Failed to find root organisation unit for active user');
        }
    }

    protected function checkSetupWizardCompleted(OrganisationUnit $rootOrganisationUnit): void
    {
        if (!$rootOrganisationUnit->getMetaData()->toArray()['setupCompleteDate']) {
            throw new SetupWizardIncomplete('User has not completed the setup wizard');
        }
        return;
    }

    protected function fetchEkmAccount(string $ekmUsername): Account
    {
        return $this->ekmAccountService->fetchByEkmUsername($ekmUsername);
    }

    protected function recreateEkmRegistrationJob(Registration $registration): void
    {
        $this->registrationService->save($registration);
        return;

    }

    protected function loginUser(string $ekmUsername): void
    {
        try {
            $user = $this->userService->fetchByUsername($ekmUsername);
        } catch(NotFound $e) {
            throw new LoginException('Failed to fetch user with username: '.$ekmUsername);
        }
        try {
            $this->loginService->loginUser($user);
        } catch(NotAuthorisedException $e) {
            throw new LoginException('Failed to authorize user with username: '.$ekmUsername);
        }
        return;
    }
}
