<?php
namespace Ekm\Controller;

use CG\Ekm\Registration\Entity as Registration;
use CG\Ekm\Registration\Exception\Runtime\RegistrationFailed;
use CG\Ekm\Registration\Exception\Runtime\RegistrationPending;
use CG\Ekm\Registration\Service as RegistrationService;
use CG\Locale\PhoneNumber;
use CG\Permission\Exception as PermissionException;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Form\ErrorMessagesToViewTrait;
use CG_Login\Controller\LoginController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Ekm\Registration\Action\Login as RegistrationLoginAction;
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
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var string $cgSupportTelephoneNumber */
    protected $cgSupportTelephoneNumber;

    public function __construct(
        ActiveUserInterface $activeUser,
        RegistrationService $registrationService,
        RegistrationLoginAction $registrationLoginAction,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->registrationService = $registrationService;
        $this->registrationLoginAction = $registrationLoginAction;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->cgSupportTelephoneNumber = PhoneNumber::getForLocale($activeUser->getLocale());
    }

    public function indexAction()
    {
        /** @var int $status */
        $status = $this->params()->fromQuery('status', null);

        /** @var string $token */
        if (!$token = $this->getToken()) {
            return $this->failedAction();
        }

        try {
            [$redirectRoute, $redirectParams] = ($this->registrationLoginAction)($token);
            return $this->redirect()->toRoute($redirectRoute, $redirectParams);
        } catch(RegistrationPending $e) {
            if (isset($status) && $status == static::STATUS_FAILED) {
                return $this->failedAction();
            }
            return $this->pendingAction($token, $status);
        } catch(RegistrationFailed $e) {
            return $this->failedAction();
        } catch(NotFound $e) {
            return $this->redirect()->toRoute(LoginController::ROUTE_PROMPT);
        } catch (PermissionException $e) {
            throw $e;
        } catch(\Exception $e) {
            $this->logErrorException($e, static::LOG_MSG_REGISTRATION_LOGIN_ERROR, ['token' => $token], [static::LOG_CODE, static::LOG_CODE_REGISTRATION_LOGIN_ATTEMPT]);
            return $this->failedAction();
        }
    }

    protected function getToken(): ?string
    {
        /** @var string $token */
        $token = $this->params()->fromQuery('token', null);
        return rawurldecode($token);
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
        $view->setTemplate('ekm_register_pending');
        $ekmPoweredByCg = $this->viewModelFactory->newInstance();
        $ekmPoweredByCg->setTemplate('channel_ekm_powered_by_cg');
        $view->addChild($ekmPoweredByCg, 'ekmPoweredByCg');
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

        /** @var ViewModel $view */
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('ekm_register_failed');
        $ekmPoweredByCg = $this->viewModelFactory->newInstance();
        $ekmPoweredByCg->setTemplate('channel_ekm_powered_by_cg');
        $view->addChild($ekmPoweredByCg, 'ekmPoweredByCg');
        $view->setVariables([
            'ekmUsername' => $ekmUsername,
            'failedMessage' => 'Unfortunately, we could not complete your registration.',
            'statusTitle' => $this->translate('What happened'),
            'statusMessage1' => $this->translate('We were unable to connect your EKM account to ChannelGrabber.'),
            'statusMessage2' => $this->translate('Please contact customer support on: '.$this->cgSupportTelephoneNumber.'.'),
            'statusMessage3' => $this->translate('Our friendly support staff will assist you further in connecting your EKM account to ChannelGrabber.'),
        ]);
        return $view;
    }

    public function checkStatusAction(): JsonModel
    {
        /** @var JsonModel $status */
        $status = $this->jsonModelFactory->newInstance();

        /** @var string $error */
        $error = null;

        /** @var string $token */
        if (!$token = $this->getToken()) {
            $error = 'Token not provided';
        }

        try {
            /** @var Registration $registration */
            $registration = $this->fetchRegistration($token);
            if (!$registration->getOrganisationUnitId()) {
                $this->registrationService->createEkmRegistrationGearmanJob($registration->getEkmUsername(), $registration->getToken());
            }
        } catch(NotFound $e) {
            $error = 'Registration could not be found';
        }

        $status->setVariable('complete', ((isset($registration) && $registration->getOrganisationUnitId()) ? true : false));
        $status->setVariable('error', $error);
        return $status;
    }
}
