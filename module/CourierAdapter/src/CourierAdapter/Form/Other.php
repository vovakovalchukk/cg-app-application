<?php

namespace CourierAdapter\Form;

use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CourierAdapter\FormAbstract;
use Zend\View\Model\ViewModel;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CourierAdapter\Module;


class Other extends FormAbstract
{
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
}