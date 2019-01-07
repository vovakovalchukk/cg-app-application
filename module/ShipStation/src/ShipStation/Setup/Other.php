<?php
namespace ShipStation\Setup;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Locale\CountryNameByCode;
use CG\Channel\Shipping\Provider\Carrier\Entity as Carrier;
use CG\Channel\Shipping\Provider\Carrier\Field;
use CG\ShipStation\Credentials;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use ShipStation\Controller\AccountController;
use ShipStation\Module;
use ShipStation\SetupInterface;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\View\Model\ViewModel;

class Other implements SetupInterface
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Url */
    protected $urlHelper;

    public function __construct(ViewModelFactory $viewModelFactory, Url $urlHelper)
    {
        $this->viewModelFactory = $viewModelFactory;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(
        Carrier $carrier,
        int $organisationUnitId,
        Account $account = null,
        Credentials $credentials = null
    ): ViewModel {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('ship-station/setup')
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('isSidebarVisible', false)
            ->setVariable('accountId', $account ? $account->getId() : null)
            ->setVariable('channelName', $carrier->getChannelName())
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        $goBackUrl = $this->urlHelper->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        if ($account) {
            $goBackUrl .= '/'.$account->getId();
        }
        $view->setVariable('goBackUrl', $goBackUrl);

        $saveRoute = implode('/', [Module::ROUTE, AccountController::ROUTE, AccountController::ROUTE_SAVE]);
        $saveUrl = $this->urlHelper->fromRoute($saveRoute, ['channel' => $carrier->getChannelName()]);
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

    protected function getButtonView(string $id, string $text): ViewModel
    {
        $buttonView = $this->viewModelFactory->newInstance([
            'buttons' => true,
            'value' => $text,
            'id' => $id
        ]);
        $buttonView->setTemplate('elements/buttons.mustache');
        return $buttonView;
    }

    protected function getTextView(string $id, string $label, ?string $value = '', bool $required = false): ViewModel
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

    protected function getCheckboxView(string $id, string $label, ?bool $selected = false, bool $required = false): ViewModel
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

    protected function getPasswordView(string $id, string $label, ?string $value = '', bool $required = false): ViewModel
    {
        $passwordView = $this->getTextView($id, $label, $value, $required);
        $passwordView->setVariable('type', 'password');
        return $passwordView;
    }

    protected function getHiddenView(string $id, string $label, ?string $value = '', bool $required = false): ViewModel
    {
        $hiddenView = $this->getTextView($id, $label, $value, $required);
        $hiddenView->setVariable('type', 'hidden');
        return $hiddenView;
    }

    protected function getCountryView(string $id, string $label, ?string $value = null, bool $required = false): ViewModel
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

    protected function getDateView(string $id, string $label, ?string $value = null, bool $required = false): ViewModel
    {
        $dateView = $this->viewModelFactory->newInstance([
            'name' => $id,
            'id' => $id,
            'label' => $label,
            'class' => ($required ? 'required' : ''),
            'value' => $value
        ]);
        $dateView->setTemplate('elements/date.mustache');
        return $dateView;
    }
}