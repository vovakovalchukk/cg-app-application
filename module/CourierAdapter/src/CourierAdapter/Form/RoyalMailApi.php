<?php

namespace CourierAdapter\Form;

use CourierAdapter\FormAbstract;
use Zend\View\Model\ViewModel;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CourierAdapter\Module;
use CG\Stdlib\Exception\Runtime\NotFound;

class RoyalMailApi extends FormAbstract
{
    public function getFormView(string $channelName, string $goBackUrl, string $saveUrl, ?int $accountId = null): ViewModel
    {
        if (!$accountId) {
            return $this->getNewAccountForm($channelName, $goBackUrl, $saveUrl, $accountId);
        }
        return $this->getCredentialsForm($channelName, $goBackUrl, $saveUrl, $accountId);
    }

    public function getNewAccountForm(string $channelName, string $goBackUrl, string $saveUrl, ?int $accountId = null)
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

    public function getCredentialsForm(string $channelName, int $accountId, string $goBackUrl, string $saveUrl)
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
}