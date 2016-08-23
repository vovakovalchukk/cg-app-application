<?php
namespace Settings\Controller\CourierAdapter;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\TestModeInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProviderController extends AbstractActionController
{
    use PrepareAdapterImplementationFieldsTrait;

    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountSetup */
    protected $caAccountSetup;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CAAccountSetup $caAccountSetup,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountSetup($caAccountSetup)
            ->setCaAccountMapper($caAccountMapper);
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);

        if ($courierInstance instanceof CredentialRequestInterface
            && ($account->getPending() || $this->isCAAccountInTestMode($caAccount, $courierInstance))
        ) {
            $pendingInstructions = $courierInstance->getAccountPendingInstructions();
            $view->setVariable('accountPendingInstructions', $pendingInstructions);
        }

        if ($account->getActive() && !$account->getPending()
            && $courierInstance instanceof ConfigInterface
            && (!$courierInstance instanceof TestModeInterface || !$courierInstance->isAccountInTestMode($caAccount))
        ) {
            $this->addConfigVariablesToChannelSpecificView($account, $view, $courierInstance);
        }

        if ($account->getActive() && !$account->getPending()
            && $this->isCAAccountInTestMode($caAccount, $courierInstance)
        ) {
            $this->addTestModeVariablesToChannelSpecificView($account, $view, $courierInstance);
            if ($courierInstance instanceof TestPackInterface) {
                $this->addTestPackVariablesToChannelSpecificView($account, $view, $courierInstance);
            }
        }

        $setupUrl = $this->caAccountSetup->getInitialisationUrl($account, '');
        $view->setVariable('url', $setupUrl);
    }

    protected function addConfigVariablesToChannelSpecificView(
        Account $account,
        ViewModel $view,
        CourierInterface $courierInstance
    ) {
        $form = $courierInstance->getConfigForm();
        $values = [];
        if (isset($account->getExternalData()['config'])) {
            $values = json_decode($account->getExternalData()['config'], true);
        }
        $this->prepareAdapterImplementationFormForDisplay($form, $values);
        $view->setVariable('configForm', $form);
    }

    protected function addTestPackVariablesToChannelSpecificView(
        Account $account,
        ViewModel $view,
        CourierInterface $courierInstance
    ) {
        $files = $courierInstance->getTestPackFileList();
        $view->setVariable('testPackFiles', $files);
    }

    protected function addTestModeVariablesToChannelSpecificView(
        Account $account,
        ViewModel $view,
        CourierInterface $courierInstance
    ) {
        $view->setVariable('testModeInstructions', 'Add your live credentials by clicking "Renew Connection".');
    }

    protected function isCAAccountInTestMode($caAccount, $courierInstance)
    {
        return $courierInstance instanceof TestModeInterface && $courierInstance->isAccountInTestMode($caAccount);
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCAAccountSetup(CAAccountSetup $caAccountSetup)
    {
        $this->caAccountSetup = $caAccountSetup;
        return $this;
    }

    protected function setCaAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }
}