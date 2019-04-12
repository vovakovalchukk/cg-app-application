<?php

namespace CourierAdapter\Form;

use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CourierAdapter\FormAbstract;
use Zend\View\Model\ViewModel;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use InvalidArgumentException;
use Zend\Form\Form as ZendForm;
use CourierAdapter\Module;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;

class Other extends FormAbstract
{
    use PrepareAdapterImplementationFieldsTrait;

    public function getFormView(string $channelName, int $accountId, string $goBackUrl, string $saveUrl): ViewModel
    {
        $adapter = $this->adapterImplementationService->getAdapterImplementationByChannelName($channelName);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $channelName, LocalAuthInterface::class
        );

        if (!$accountId && $courierInstance instanceof CredentialRequestInterface) {
            $requestUri = $this->url()->fromRoute(Module::ROUTE . '/' . CAAccountSetup::ROUTE_REQUEST, ['channel' => $channelName]);
            $preInstructions = '<h1>Do you need to request your ' . $adapter->getDisplayName() . ' credentials?</h1>';
            $preInstructions .= '<p><a href="' . $requestUri . '">Request your credentials</a></p>';
        }

        $form = $courierInstance->getCredentialsForm();
        $values = [];
        if ($accountId) {
            $values = $this->caModuleAccountService->getCredentialsArrayForAccount($accountId);
        }
        $this->prepareAdapterImplementationFormForDisplay($form, $values);
        $view = $this->getAdapterFieldsView(
            $form,
            $channelName,
            $goBackUrl,
            $saveUrl,
            'Saving credentials',
            'Credentials saved',
            $accountId
        );
        if (isset($preInstructions)) {
            $view->setVariable('preInstructions', $preInstructions);
        }

        return $view;
    }

    /**
     * @return Entity
     */
    public function getAdapterImplementationByChannelName($channelName)
    {
        $adapterImplementationsByChannelName = $this->adapterImplementations->getBy('channelName', $channelName);
        if (count($adapterImplementationsByChannelName) == 0) {
            throw new InvalidArgumentException('Adapter with channel name "'.$channelName.'" not found');
        }
        $adapterImplementationsByChannelName->rewind();
        return $adapterImplementationsByChannelName->current();
    }

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