<?php
namespace Settings\Controller\CourierAdapter;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProviderController extends AbstractActionController
{
    /** @var AdapterService */
    protected $adapterService;
    /** @var CAAccountSetup */
    protected $caAccountSetup;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(
        AdapterService $adapterService,
        CAAccountSetup $caAccountSetup,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setAdapterService($adapterService)
            ->setCAAccountSetup($caAccountSetup)
            ->setCaAccountMapper($caAccountMapper);
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $courierInterface = $this->adapterService->getAdapterCourierInterfaceForAccount($account);
        if ($account->getPending() && $courierInterface instanceof CredentialRequestInterface) {
            $pendingInstructions = $courierInterface->getAccountPendingInstructions();
            $view->setVariable('accountPendingInstructions', $pendingInstructions);
            return;
        }
        if ($account->getActive() && $courierInterface instanceof ConfigInterface) {
            $this->addConfigVariablesToChannelSpecificView($account, $view, $courierInterface);
        }
        if ($account->getActive() && $courierInterface instanceof TestPackInterface) {
            $this->addTestPackVariablesToChannelSpecificView($account, $view, $courierInterface);
        }

        $setupUrl = $this->caAccountSetup->getInitialisationUrl($account, '');
        $view->setVariable('url', $setupUrl);
    }

    protected function addConfigVariablesToChannelSpecificView(
        Account $account,
        ViewModel $view,
        CourierInterface $courierInterface
    ) {
        $fields = $courierInterface->getConfigFields();
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
        CourierInterface $courierInterface
    ) {
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        if (!$courierInterface->isAccountInTestMode($caAccount)) {
            return;
        }
        $files = $courierInterface->getTestPackFileList();
        $view->setVariable('testPackFiles', $files);
    }

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
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