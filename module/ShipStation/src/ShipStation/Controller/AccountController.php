<?php
namespace ShipStation\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Channel\Type as ChannelType;
use CG\Locale\CountryNameByCode;
use CG\ShipStation\Account\CreationService as AccountCreationService;
use CG\ShipStation\Carrier\Entity as Carrier;
use CG\ShipStation\Carrier\Field;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\ActiveUserInterface;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use ShipStation\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    const ROUTE = 'Account';
    const ROUTE_SAVE = 'Save';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var CarrierService */
    protected $carrierService;
    /** @var AccountService */
    protected $accountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        CarrierService $carrierService,
        AccountService $accountService,
        Cryptor $cryptor,
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->carrierService = $carrierService;
        $this->accountService = $accountService;
        $this->cryptor = $cryptor;
        $this->accountCreationService = $accountCreationService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function setupAction(): ViewModel
    {
        $channelName = $this->params('channel');
        $carrier = $this->carrierService->getCarrierByChannelName($channelName);
        $accountId = $this->params()->fromQuery('accountId');
        $credentials = null;
        if ($accountId) {
            $account = $this->accountService->fetch($accountId);
            $credentials = $this->cryptor->decrypt($account->getCredentials());
        }

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('ship-station/setup')
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('isSidebarVisible', false)
            ->setVariable('accountId', $accountId)
            ->setVariable('channelName', $channelName)
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        $goBackUrl = $this->plugin('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        if ($accountId) {
            $goBackUrl .= '/'.$accountId;
        }
        $view->setVariable('goBackUrl', $goBackUrl);

        $saveRoute = implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_SAVE]);
        $saveUrl = $this->url()->fromRoute($saveRoute, ['channel' => $channelName]);
        $view->setVariable('saveUrl', $saveUrl);

        $this->addCarrierFieldsToView($view, $carrier, $credentials);

        return $view;
    }

    protected function getAccountRoute()
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }

    protected function addCarrierFieldsToView(ViewModel $view, Carrier $carrier, $credentials = null)
    {
        $fieldViews = [];
        /** @var Field $field */
        foreach ($carrier->getFields() as $field) {
            $fieldValue = ($credentials ? $credentials->get($field->getName()) : $field->getValue());
            $inputTypeGetter = 'get'.ucfirst($field->getInputType()).'View';
            if (!method_exists($this, $inputTypeGetter)) {
                $inputTypeGetter = 'getTextView';
            }
            $fieldViews[] = $this->$inputTypeGetter($field->getName(), $field->getLabel(), $fieldValue, $field->isRequired());
        }
        $view->setVariable('fieldViews', $fieldViews);
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

    protected function getTextView($id, $label, $value = '', $required = false)
    {
        $textView = $this->viewModelFactory->newInstance([
            'name' => $id,
            'id' => $id,
            'label' => $label,
            'value' => $value,
            'class' => ($required ? 'required' : ''),
        ]);
        $textView->setTemplate('elements/text.mustache');
        return $textView;
    }

    protected function getCheckboxView($id, $label, $selected = false, $required = false)
    {
        $checkboxView = $this->viewModelFactory->newInstance([
            'id' => $id,
            'label' => $label,
            'selected' => $selected,
            'class' => ($required ? 'required' : '')
        ]);
        $checkboxView->setTemplate('elements/checkbox.mustache');
        return $checkboxView;
    }

    protected function getPasswordView($id, $label, $value = '', $required = false)
    {
        $passwordView = $this->getTextView($id, $label, $value, $required);
        $passwordView->setVariable('type', 'password');
        return $passwordView;
    }

    protected function getHiddenView($id, $label, $value = '', $required = false)
    {
        $hiddenView = $this->getTextView($id, $label, $value, $required);
        $hiddenView->setVariable('type', 'hidden');
        return $hiddenView;
    }

    protected function getCountryView($id, $label, $value = null, $required = false)
    {
        $options = [];
        foreach (CountryNameByCode::getCountryCodeToNameMap() as $code => $name) {
            $options[] = [
                'value' => $code,
                'title' => $name,
                'selected' => ($code == $value),
            ];
        }
        $selectView = $this->viewModelFactory->newInstance([
            'name' => $id,
            'id' => $id,
            'label' => $label,
            'class' => ($required ? 'required' : ''),
            'searchField' => true,
            'options' => $options
        ]);
        $selectView->setTemplate('elements/custom-select.mustache');
        return $selectView;
    }

    public function saveAction()
    {
        // ZF2 replaces spaces in param names with underscores, need to undo that
        $params = [];
        foreach ($this->params()->fromPost() as $key => $value) {
            $params[str_replace('_', ' ', $key)] = $value;
        }
        $view = $this->jsonModelFactory->newInstance();
        $accountEntity = $this->accountCreationService->connectAccount(
            $this->activeUserContainer->getActiveUser()->getOrganisationUnitId(),
            $params['account'],
            $params
        );
        $url = $this->plugin('url')->fromRoute($this->getAccountRoute(), ["account" => $accountEntity->getId(), "type" => ChannelType::SHIPPING]);
        $url .= '/' . $accountEntity->getId();
        $view->setVariable('redirectUrl', $url);
        return $view;
    }
}