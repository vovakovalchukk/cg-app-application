<?php
namespace Settings\Controller\CourierAdapter;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProviderController extends AbstractActionController
{
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
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        if ($account->getPending() && $courierInstance instanceof CredentialRequestInterface) {
            $pendingInstructions = $courierInstance->getAccountPendingInstructions();
            $view->setVariable('accountPendingInstructions', $pendingInstructions);
            return;
        }
        if ($account->getActive() && $courierInstance instanceof ConfigInterface) {
            $this->addConfigVariablesToChannelSpecificView($account, $view, $courierInstance);
        }
        if ($account->getActive() && $courierInstance instanceof TestPackInterface) {
            $this->addTestPackVariablesToChannelSpecificView($account, $view, $courierInstance);
        }

        $setupUrl = $this->caAccountSetup->getInitialisationUrl($account, '');
        $view->setVariable('url', $setupUrl);
    }

    protected function addConfigVariablesToChannelSpecificView(
        Account $account,
        ViewModel $view,
        CourierInterface $courierInstance
    ) {
        $fields = $courierInstance->getConfigFields();
        $values = [];
        if (isset($account->getExternalData()['config'])) {
            $values = json_decode($account->getExternalData()['config'], true);
        }
        $this->prepareAdapterFields($fields, $values);
        $view->setVariable('configFields', $fields);
    }

    protected function prepareAdapterFields(array $fields, array $values = [])
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field->getOption('required')) {
                $class = $field->getAttribute('class') ?: '';
                $field->setAttribute('class', $class . ' required');
            }
            if (isset($values[$field->getName()])) {
                $field->setValue($values[$field->getName()]);
            }
        }
    }

    protected function addTestPackVariablesToChannelSpecificView(
        Account $account,
        ViewModel $view,
        CourierInterface $courierInstance
    ) {
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        if (!$courierInstance->isAccountInTestMode($caAccount)) {
            return;
        }
        $files = $courierInstance->getTestPackFileList();
        $view->setVariable('testPackFiles', $files);
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