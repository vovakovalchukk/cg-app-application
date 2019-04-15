<?php

namespace CourierAdapter\Form;

use CourierAdapter\FormAbstract;
use Zend\View\Model\ViewModel;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CourierAdapter\Module;
use CG\OrganisationUnit\Entity as OrganisationUnitEntity;
use Zend\Form\Form as ZendForm;

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
        $activeUser = $this->activeUserContainer->getActiveUser();
        /** @var OrganisationUnitEntity $organisationUnit */
        $organisationUnit = $this->organisationUnitService->fetch($activeUser->getId());
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $channelName, LocalAuthInterface::class
        );
        $form = $courierInstance->getFirstTimeAccountForm();
        $values = [
            'companyName' => $organisationUnit->getAddressCompanyName(),
            'addressLine1' => $organisationUnit->getAddress1(),
            'addressLine2' => $organisationUnit->getAddress2(),
            'addressLine3' => $organisationUnit->getAddress3(),
            'town' => $organisationUnit->getAddressCity(),
            'county' => $organisationUnit->getAddressCounty(),
            'postcode' => $organisationUnit->getAddressPostcode(),
            'contactName' => $activeUser->getFirstName() . ' ' . $activeUser->getLastName(),
            'phoneNumber' => $organisationUnit->getPhoneNumber(),
            'emailAddress' => $activeUser->getUsername(),
        ];
        if ($accountId) {
            $values = $this->caModuleAccountService->getCredentialsArrayForAccount($accountId);
        }
        $this->prepareAdapterImplementationFormForDisplay($form, $values);
        $view = $this->getAdapterFieldsViewForFirstTime(
            $form,
            $channelName,
            $goBackUrl,
            '/mark/test/lol',
            'Submitting Details',
            'Details Submitted',
            $accountId
        );
        return $view;
    }

    protected function getAdapterFieldsViewForFirstTime(
        ZendForm $form,
        $channelName,
        string $goBackUrl,
        string $saveUrl,
        $savingNotification = null,
        $savedNotification = null,
        $accountId = null
    ) {
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
            ->addChild($this->getButtonView('submitDetails', 'Submit Details'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        return $view;
    }

    public function getCredentialsForm(string $channelName, string $goBackUrl, string $saveUrl, ?int $accountId)
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