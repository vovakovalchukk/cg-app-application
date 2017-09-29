<?php
namespace CG_Register\Controller;

use CG\Ekm\Registration\Action\Login as RegistrationLoginAction;
use CG\Ekm\Registration\Entity as Registration;
use CG\Ekm\Registration\Exception\Runtime\RegistrationCompleteForLoggedInUser;
use CG\Ekm\Registration\Exception\Runtime\RegistrationCompleteForLoggedOutUser;
use CG\Ekm\Registration\Exception\Runtime\RegistrationFailed;
use CG\Ekm\Registration\Exception\Runtime\RegistrationPending;
use CG\Ekm\Registration\Service as RegistrationService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Zend\Stdlib\Form\ErrorMessagesToViewTrait;
use CG_Login\Controller\LoginController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Module as OrdersModule;
use SetupWizard\Controller\ChannelsController as SetupWizardChannelsController;
use SetupWizard\Module as SetupWizardModule;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class EkmRegistrationController extends AbstractActionController implements LoggerAwareInterface
{
    use ErrorMessagesToViewTrait;
    use LogTrait;

    const ROUTE = 'cg_ekm_register';
    const ROUTE_STATUS_CHECK = 'cg_ekm_register_status_check';

    const STATUS_FAILED = 0;

    const LOG_CODE = 'EkmRegistrationController';
    const LOG_CODE_REGISTRATION_LOGIN_ATTEMPT = 'EkmRegistrationLoginAttempt';
    const LOG_MSG_REGISTRATION_LOGIN_ERROR = 'Failed to login user, an exception occurred. Token: %s';

    /** @var RegistrationService $registrationService */
    protected $registrationService;
    /** @var RegistrationLoginAction $registrationLoginAction */
    protected $registrationLoginAction;
    /** @var ViewModelFactory $viewModel */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        RegistrationService $registrationService,
        RegistrationLoginAction $registrationLoginAction,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->registrationService = $registrationService;
        $this->registrationLoginAction = $registrationLoginAction;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function indexAction()
    {
        $requestQuery = $this->getRequest()->getQuery()->toArray();

        /** @var int $status */
        $status = isset($requestQuery['status']) ? $requestQuery['status'] : null;

        /** @var string $token */
        if (!$token = $this->getToken()) {
            return $this->failedAction();
        }

        try {
            $registration = $this->registrationLoginAction->__invoke($token);
        } catch(RegistrationPending $e) {
            if (isset($status) && $status == static::STATUS_FAILED) {
                return $this->failedAction();
            }
            return $this->pendingAction($token, $status);
        } catch(RegistrationFailed $e) {
            return $this->failedAction();
        } catch(RegistrationCompleteForLoggedInUser $e) {
            return $this->redirect()->toRoute(OrdersModule::ROUTE);
        } catch(RegistrationCompleteForLoggedOutUser $e) {
            return $this->redirect()->toRoute(LoginController::ROUTE_PROMPT);
        } catch(Exception $e) {
            $this->logErrorException($e, static::LOG_MSG_REGISTRATION_LOGIN_ERROR, ['token' => $token], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_LOGIN_ATTEMPT]);
            return $this->failedAction();
        }

        // No-op: Setup Wizard takes over - handles redirect
        return $this->redirect()->toRoute(SetupWizardModule::ROUTE . '/' . SetupWizardChannelsController::ROUTE_CHANNELS . '/' . SetupWizardChannelsController::ROUTE_CHANNEL_PICK);
    }

    protected function getToken(): ?string
    {
        /** @var array $requestQuery */
        $requestQuery = $this->getRequest()->getQuery()->toArray();

        /** @var string $token */
        $token = isset($requestQuery['token']) ? $requestQuery['token'] : null;

        return urldecode($token);
    }

    protected function fetchRegistration(string $token): Registration
    {
        return $this->registrationService->fetchByToken($token);
    }

    protected function pendingAction(string $token): ViewModel
    {
        try {
            /** @var Registration $registration */
            $registration = $this->fetchRegistration($token);
        } catch(NotFound $e) {
            return $this->failedAction();
        }

        /** @var ViewModel $view */
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('cg_register_ekm_register_pending');
        $view->setVariables([
            'ekmUsername' => $registration->getEkmUsername(),
            'pleaseWaitMessage' => $this->translate('Please be patient, you will be enjoying ChannelGrabber shortly.'),
            'pleaseWaitLoaderText' => $this->translate('Checking registration status'),
            'statusTitle' => $this->translate('What we are doing'),
            'statusMessage1' => $this->translate('1. Setting up your ChannelGrabber account'),
            'statusMessage2' => $this->translate('2. Connecting your EKM account'),
            'refreshMessage' => $this->translate('This page will auto-refresh when your registration completes.'),
            'usernameText' => $this->translate('Username')
        ]);
        return $view;
    }

    protected function failedAction(string $token = null): ViewModel
    {
        /** @var string $ekmUsername */
        $ekmUsername = null;

        if ($token) {
            try {
                /** @var Registration $registration */
                $registration = $this->fetchRegistration($token);
                $ekmUsername = $registration->getEkmUsername();
            } catch(NotFound $e) {
                // No-op
            }
        }
        /** @var string $customerSupportPhoneNumber */
        $customerSupportPhoneNumber = '0152 222 4589';

        /** @var ViewModel $view */
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('cg_register_ekm_register_failed');
        $view->setVariables([
            'ekmUsername' => $ekmUsername,
            'failedMessage' => 'Unfortunately, we could not complete your registration.',
            'statusTitle' => $this->translate('What happened'),
            'statusMessage1' => $this->translate('We were unable to connect your EKM account to ChannelGrabber.'),
            'statusMessage2' => $this->translate('Please contact customer support on: '.$customerSupportPhoneNumber.'.'),
            'statusMessage3' => $this->translate('Our friendly support staff will assist you further in connecting your EKM account to ChannelGrabber.'),
        ]);
        return $view;
    }

    public function checkStatusAction(): JsonModel
    {
        /** @var JsonModel $status */
        $status = $this->jsonModelFactory->newInstance();

        /** @var bool $error */
        $error = true;

        /** @var string $token */
        if (!$token = $this->getToken()) {
            $error = 'Token not provided';
        }

        /** @var bool $isRegistrationComplete */
        $isRegistrationComplete = false;

        try {
            /** @var Registration $registration */
            $registration = $this->fetchRegistration($token);
            $isRegistrationComplete = ($registration->getOrganisationUnitId() ? true : false);
            if (!$registration->getOrganisationUnitId()) {
                $this->registrationLoginAction->recreateEkmRegistrationJob($registration);
            }
        } catch(NotFound $e) {
            $error = 'Registration could not be found';
        }

        $status->setVariable('complete', $isRegistrationComplete);
        $status->setVariable('error', $error);
        return $status;
    }
}
