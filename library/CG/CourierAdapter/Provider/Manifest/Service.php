<?php
namespace CG\CourierAdapter\Provider\Manifest;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Entity as AccountManifest;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\CourierAdapter\Manifest\GeneratingInterface as ManifestGeneratingInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'CourierAdapterManifestService';

    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper);
    }

    public function createManifestForAccount(Account $account, AccountManifest $accountManifest)
    {
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        if (!$courierInstance instanceof ManifestGeneratingInterface) {
            throw new \RuntimeException('Manifest generation requested but the courier instance does not support it');
        }

        try {
            $caAccount = $this->caAccountMapper->fromOHAccount($account);
            $manifest = $courierInstance->generateManifest($caAccount);
        } catch (OperationFailed $e) {
            $this->logWarningException($e, 'Courier instance threw exception when generating manifest', [], [static::LOG_CODE, 'Exception']);
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
        
        $accountManifest->setExternalId($manifest->getReference())
            ->setManifest($manifest->getData());
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
