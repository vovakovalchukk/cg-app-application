<?php
namespace CourierAdapter\Account;

use CG\Account\Client\Service as OHAccountService;
use CG\Account\Credentials\Cryptor;
use CG\CourierAdapter\Account\ConfigInterface;
use CG\CourierAdapter\Account\CredentialVerificationInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Create as CALabelCreateService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Client\Service as OHOrderService;
use InvalidArgumentException;
use Zend\Form\Form as ZendForm;

class Service
{
    use PrepareAdapterImplementationFieldsTrait;

    /** @var OHAccountService */
    protected $ohAccountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(
        OHAccountService $ohAccountService,
        Cryptor $cryptor,
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setOHAccountService($ohAccountService)
            ->setCryptor($cryptor)
            ->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper);
    }

    /**
     * @return array
     */
    public function getCredentialsArrayForAccount($accountId)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->toArray();
    }

    /**
     * @return bool
     */
    public function validateSetupForm(ZendForm $form, CourierInterface $courierInstance, $accountId = null)
    {
        if (!$form->isValid()) {
            return false;
        }

        if (!$courierInstance instanceof CredentialVerificationInterface) {
            return true;
        }

        $caAccountData = ['credentials' => $form->getData()];
        if ($accountId) {
            $account = $this->ohAccountService->fetch($accountId);
            $caAccountData['id'] = $account->getExternalId();
        }

        $caAccount = $this->caAccountMapper->fromArray($caAccountData);
        return $courierInstance->validateCredentials($caAccount);
    }

    public function saveConfigForAccount($accountId, array $config)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForChannel(
            $account->getChannel(), ConfigInterface::class
        );

        $form = $courierInstance->getConfigForm();
        $this->prepareAdapterImplementationFormForSubmission($form, $config);

        if (!$form->isValid()) {
            return $form->getMessages();
        }
        $formData = $form->getData();

        $externalData = $account->getExternalData();
        $externalData['config'] = json_encode($formData);

        try {
            $account->setExternalData($externalData);
            $this->ohAccountService->save($account);
        } catch (NotModified $e) {
            // No-op
        }
        return true;
    }

    protected function setOHAccountService(OHAccountService $ohAccountService)
    {
        $this->ohAccountService = $ohAccountService;
        return $this;
    }

    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCAAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }
}