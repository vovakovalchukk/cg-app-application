<?php
namespace CourierAdapter;

use CG\Account\Client\Service as AccountService;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Address\Mapper as CAAddressMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CourierAdapter\Account\Service as CAModuleAccountService;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\PluginManager as PluginManager;
use Zend\Form\Form as ZendForm;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;

abstract class FormAbstract implements FormInterface
{
    use PrepareAdapterImplementationFieldsTrait;

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
    /** @var PluginManager */
    protected $pluginManager;

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
        PluginManager $pluginManager
    ) {
        $this->adapterImplementationService = $adapterImplementationService;
        $this->accountCreationService = $accountCreationService;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
        $this->caModuleAccountService = $caModuleAccountService;
        $this->caAddressMapper = $caAddressMapper;
        $this->organisationUnitService = $organisationUnitService;
        $this->accountService = $accountService;
        $this->caAccountMapper = $caAccountMapper;
        $this->pluginManager = $pluginManager;
    }

    abstract public function getFormView(string $shippingChannel, int $accountId,  string $goBackUrl, string $saveUrl): ViewModel;

    protected function prepareAdapterImplementationFormForDisplay(ZendForm $form, array $values = [])
    {
        $fieldsOrSets = array_merge($form->getFieldsets(), $form->getElements());
        $this->prepareAdapterImplementationFieldsForDisplay($fieldsOrSets, $values);

        if (!empty($values)) {
            $form->setData($values);
        }

        $form->prepare();
        // ZendFrom will remove any password values on prepare()
        $this->reAddPasswordFieldValues($fieldsOrSets, $values);
    }

    protected function getAdapterFieldsView(
        ZendForm $form,
        $channelName,
        string $goBackUrl,
        string $saveUrl,
        $savingNotification = null,
        $savedNotification = null,
        $accountId = null
    ) {
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

    protected function getAccountRoute()
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }
}