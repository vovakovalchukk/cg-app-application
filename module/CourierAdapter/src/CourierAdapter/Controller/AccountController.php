<?php
namespace CourierAdapter\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Exception\InvalidCredentialsException;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Address\Mapper as CAAddressMapper;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CourierAdapter\Account\Email\Service as SupportEmailService;
use CourierAdapter\Account\Service as CAModuleAccountService;
use CourierAdapter\Form\Factory as FormFactory;
use CourierAdapter\FormInterface;
use CourierAdapter\Module;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Form\Element\Hidden as ZendHiddenElement;
use Zend\Form\Form as ZendForm;
use Zend\Mvc\Controller\AbstractActionController;
use CG\CourierAdapter\StorageInterface;

class AccountController extends AbstractActionController
{
    use PrepareAdapterImplementationFieldsTrait;

    const ROUTE = 'Account';
    const ROUTE_SAVE = 'Save';
    const ROUTE_REQUEST_SEND = 'Send';
    const ROUTE_SAVE_CONFIG = 'Save Config';
    const ROUTE_TEST_PACK_FILE = 'Test Pack File';
    const ROUTE_REQUEST_CONNECTION = 'Request Connection';

    const ACCOUNT_CONNECTION_REQUEST_KEY_TEMPLATE = '%s-%s-accountConnectionData';

    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var CAModuleAccountService */
    protected $caModuleAccountService;
    /** @var CAAddressMapper */
    protected $caAddressMapper;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var AccountService */
    protected $accountService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var FormFactory */
    protected $formFactory;
    /** @var SupportEmailService */
    protected $supportEmailService;
    /** @var StorageInterface */
    protected $storage;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        AccountCreationService $accountCreationService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        CAModuleAccountService $caModuleAccountService,
        CAAddressMapper $caAddressMapper,
        OrganisationUnitService $organisationUnitService,
        AccountService $accountService,
        CAAccountMapper $caAccountMapper,
        FormFactory $formFactory,
        SupportEmailService $supportEmailService,
        StorageInterface $storage
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setAccountCreationService($accountCreationService)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setCaModuleAccountService($caModuleAccountService)
            ->setCAAddressMapper($caAddressMapper)
            ->setOrganisationUnitService($organisationUnitService)
            ->setAccountService($accountService)
            ->setCAAccountMapper($caAccountMapper)
            ->setFormFactory($formFactory)
            ->setSupportEmailService($supportEmailService)
            ->setStorage($storage);
    }

    public function setupAction()
    {
        $channelName = $this->params('channel');
        $accountId = $this->params()->fromQuery('accountId');
        $goBackUrl = $this->getPluginManager()->get('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        $saveRoute = implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_SAVE]);
        $saveUrl = $this->url()->fromRoute($saveRoute, ['channel' => $channelName]);
        /** @var FormInterface $formService */
        $formService = ($this->formFactory)($channelName);
        return $formService->getFormView($channelName, $goBackUrl, $saveUrl, $accountId);
    }

    public function requestCredentialsAction()
    {
        $channelName = $this->params('channel');
        $adapter = $this->adapterImplementationService->getAdapterImplementationByChannelName($channelName);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $channelName, CredentialRequestInterface::class
        );
        $rootOu = $this->getActiveUserRootOu();

        $credentialsUri = $this->caModuleAccountService->getCredentialsUriForNewAccount($channelName, $rootOu->getId());
        $preInstructions = '<h1>Already have your ' . $adapter->getDisplayName() . ' credentials?</h1>';
        $preInstructions .= '<p><a href="' . $credentialsUri . '">Enter your credentials</a></p>';

        $instructions = $courierInstance->getCredentialsRequestInstructions();
        $caAddress = $this->caAddressMapper->organisationUnitToCollectionAddress($rootOu);
        $form = $courierInstance->getCredentialsRequestForm($caAddress, $rootOu->getAddressCompanyName());
        $this->prepareAdapterImplementationFormForDisplay($form);

        // All the forms are processed in one place, make that easier by marking this as the request form
        $rcField = (new ZendHiddenElement(AccountCreationService::REQUEST_CREDENTIALS_FIELD))->setValue(1);
        $form->add($rcField);

        $saveRoute = implode('/', [CAAccountSetup::ROUTE, CAAccountSetup::ROUTE_REQUEST, static::ROUTE_REQUEST_SEND]);

        $view = $this->getAdapterFieldsView(
            $form,
            $channelName,
            $saveRoute,
            'Submitting request',
            'Request Submitted'
        );
        $view->setVariable('preInstructions', $preInstructions)
            ->setVariable('instructions', $instructions);

        $linkButton = $view->getChildrenByCaptureTo('linkAccount')[0];
        $linkButton->setVariable('value', 'Submit Request');

        return $view;
    }

    protected function getActiveUserRootOu()
    {
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        return $this->organisationUnitService->fetch($ouId);
    }

    protected function getAdapterFieldsView(
        ZendForm $form,
        $channelName,
        $saveRoute,
        $savingNotification = null,
        $savedNotification = null,
        $accountId = null
    ) {
        $goBackUrl = $this->plugin('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        $saveUrl = $this->url()->fromRoute($saveRoute, ['channel' => $channelName]);
        if ($accountId) {
            $goBackUrl .= '/' . $accountId;
            $saveUrl .= '?accountId=' . $accountId;
        }

        $view = $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'accountId' => $accountId,
            'channelName' => $channelName,
            'saveUrl' => $saveUrl,
            'goBackUrl' => $goBackUrl,
            'form' => $form,
            'savingNotification' => $savingNotification,
            'savedNotification' => $savedNotification,
        ]);
        $view
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        return $view;
    }

    protected function getAccountRoute()
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }

    protected function getButtonView($id, $text)
    {
        $buttonView = $this->viewModelFactory->newInstance([
            'buttons' => true,
            'value' => $text,
            'id' => $id
        ]);
        $buttonView->setTemplate('elements/buttons.mustache');
        return $buttonView;
    }

    public function sendCredentialsRequestAction()
    {
        $channelName = $this->params('channel');
        $params = $this->params()->fromPost();
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $channelName, CredentialRequestInterface::class
        );

        $rootOu = $this->getActiveUserRootOu();
        $caAddress = $this->caAddressMapper->organisationUnitToCollectionAddress($rootOu);
        $form = $courierInstance->getCredentialsRequestForm($caAddress, $rootOu->getAddressCompanyName());
        $this->prepareAdapterImplementationFormForSubmission($form, $params);

        if (!$form->isValid()) {
            $view = $this->jsonModelFactory->newInstance([
                'valid' => false,
                'messages' => $form->getMessages(),
            ]);
            return $view;
        }

        $courierInstance->submitCredentialsRequestForm($form);

        $view = $this->jsonModelFactory->newInstance();
        $url = $this->connectAccountAndGetRedirectUrl($params);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    protected function connectAccountAndGetRedirectUrl(array $params)
    {
        $accountEntity = $this->connectAccount($params);
        $url = $this->getRedirectUrlForAccount($accountEntity);
        return $url;
    }

    protected function connectAccount(array $params): AccountEntity
    {
        $accountEntity = $this->accountCreationService->connectAccount(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            (isset($params['account']) ? $params['account'] : null),
            $params
        );
        return $accountEntity;
    }

    protected function getRedirectUrlForAccount(AccountEntity $accountEntity): string
    {
        $url = $this->plugin('url')->fromRoute($this->getAccountRoute(), ["account" => $accountEntity->getId(), "type" => ChannelType::SHIPPING]);
        $url .= '/' . $accountEntity->getId();
        return $url;
    }

    public function saveAction()
    {
        $params = $this->params()->fromPost();
        $channelName = $params['channel'];
        $accountId = (isset($params['account']) ? $params['account'] : null);

        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $channelName, LocalAuthInterface::class
        );
        $view = $this->jsonModelFactory->newInstance();

        $form = $courierInstance->getCredentialsForm();
        $this->prepareAdapterImplementationFormForSubmission($form, $params);

        $valid = $this->caModuleAccountService->validateSetupForm($form, $courierInstance, $accountId);
        if (!$valid) {
            throw new ValidationException('The entered credentials are invalid or incomplete. Please check them and try again.');
        }

        $url = $this->connectAccountAndGetRedirectUrl($params);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    public function saveConfigAction()
    {
        $params = $this->params()->fromPost();
        $accountId = $params['accountId'];

        $result = $this->caModuleAccountService->saveConfigForAccount($accountId, $params);

        if (is_array($result)) {
            return $this->jsonModelFactory->newInstance([
                'valid' => false,
                'messages' => $result
            ]);
        }
        return $this->jsonModelFactory->newInstance([
            'valid' => true,
            'status' => $this->translate('Changes Saved')
        ]);
    }

    public function authSuccessAction()
    {
        $channelName = $this->params('channel');
        $accountId = $this->params()->fromQuery('accountId');
        $params = $this->params()->fromPost();

        $params['channel'] = $channelName;
        if ($accountId) {
            $params['account'] = $accountId;
        }

        try {
            $url = $this->connectAccountAndGetRedirectUrl($params);
            $this->redirect()->toUrl($url);
        } catch (InvalidCredentialsException $e) {
            $this->redirect()->toRoute(CAAccountSetup::ROUTE . '/' . CAAccountSetup::ROUTE_AUTH_FAILURE, ['channel' => $channelName]);
            return;
        }
    }

    public function authFailureAction()
    {
        $this->redirect()->toRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
    }

    public function downloadTestPackFileAction()
    {
        $accountId = $this->params()->fromQuery('accountId');
        $fileReference = $this->params()->fromQuery('file');

        $account = $this->accountService->fetch($accountId);
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        $ou = $this->organisationUnitService->fetch($account->getOrganisationUnitId());
        $caAddress = $this->caAddressMapper->organisationUnitToCollectionAddress($ou);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $account->getChannel(), TestPackInterface::class
        );
        $testPackFile = $this->fetchTestPackFile($fileReference, $courierInstance);

        $dataUri = $courierInstance->generateTestPackFile($testPackFile, $caAccount, $caAddress);

        return $this->dataUriToFileResponse($dataUri, $testPackFile->getName());
    }

    public function requestConnectionAction()
    {
        $params = $this->params()->fromPost();
        $view = $this->jsonModelFactory->newInstance();
        $account = $this->connectAccount($params);
        $account->setActive(false);
        $account->setPending(false);
        $this->accountService->save($account);
        $this->storage->set($this->getRequestConnectionStorageKey($account), json_encode($params));
        $this->supportEmailService->sendAccountConnectionRequestEmail($account, $params);
        $url = $this->getRedirectUrlForAccount($account);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    protected function fetchTestPackFile($fileReference, TestPackInterface $courierInstance)
    {
        foreach ($courierInstance->getTestPackFileList() as $testPackFile) {
            if ($testPackFile->getReference() == $fileReference) {
                return $testPackFile;
            }
        }

        throw new InvalidArgumentException('No test pack file with reference "' . $fileReference . '" found');
    }

    protected function dataUriToFileResponse($dataUri, $fileName)
    {
        list($type, $data) = explode(';', $dataUri);
        list($encoding, $data) = explode(',', $data);
        if ($encoding == 'base64') {
            $data = base64_decode($data);
        }
        $mimeType = preg_replace('/^data:/', '', $type);
        list(, $fileExt) = explode('/', $mimeType);

        if (!preg_match('/\.' . $fileExt . '$/i', $fileName)) {
            $fileName .= '.' . $fileExt;
        }

        return new FileResponse($mimeType, $fileName, $data);
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setAccountCreationService(AccountCreationService $accountCreationService)
    {
        $this->accountCreationService = $accountCreationService;
        return $this;
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

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setCaModuleAccountService(CAModuleAccountService $caModuleAccountService)
    {
        $this->caModuleAccountService = $caModuleAccountService;
        return $this;
    }

    protected function setCAAddressMapper(CAAddressMapper $caAddressMapper)
    {
        $this->caAddressMapper = $caAddressMapper;
        return $this;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setCAAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }

    protected function setFormFactory(FormFactory $formFactory): AccountController
    {
        $this->formFactory= $formFactory;
        return $this;
    }

    protected function setSupportEmailService(SupportEmailService $supportEmailService): AccountController
    {
        $this->supportEmailService = $supportEmailService;
        return $this;
    }

    protected function setStorage(StorageInterface $storage): AccountController
    {
        $this->storage = $storage;
        return $this;
    }

    protected function getRequestConnectionStorageKey(AccountEntity $account)
    {
        return sprintf(static::ACCOUNT_CONNECTION_REQUEST_KEY_TEMPLATE, $account->getOrganisationUnitId(), $account->getId());
    }
}