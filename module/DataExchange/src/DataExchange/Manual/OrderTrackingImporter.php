<?php
namespace DataExchange\Manual;

use CG\DataExchange\Manual;
use CG\DataExchange\Manual\Gearman\Workload\RunManualExchange as RunManualExchangeWorkload;
use CG\DataExchange\Manual\Mapper as ManualMapper;
use CG\Order\Client\Tracking\FileStorage\StorageInterface as FileStorage;
use CG\User\ActiveUserInterface;
use GearmanClient;

class OrderTrackingImporter
{
    /** @var ManualMapper */
    protected $manualMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var FileStorage */
    protected $fileStorage;

    public function __construct(
        ManualMapper $manualMapper,
        ActiveUserInterface $activeUserContainer,
        GearmanClient $gearmanClient,
        FileStorage  $fileStorage
    ) {
        $this->manualMapper = $manualMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->gearmanClient = $gearmanClient;
        $this->fileStorage = $fileStorage;
    }

    public function __invoke(int $templateId, string $fileContents): void
    {
        $filename = $this->fileStorage->save($this->activeUserContainer->getActiveUserRootOrganisationUnitId(), $fileContents);
        $manualExchange = $this->createManualExchange($templateId, $filename);
        $workload = new RunManualExchangeWorkload(json_encode($manualExchange));
        $this->gearmanClient->doBackground(RunManualExchangeWorkload::buildFunctionNameForManual($manualExchange), serialize($workload));
    }

    protected function createManualExchange(int $templateId, string $filename): Manual
    {
        return $this->manualMapper->fromArray([
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'userId' => $this->activeUserContainer->getActiveUser()->getId(),
            'operation' => Manual::OPERATION_IMPORT,
            'type' => Manual::TYPE_ORDER_TRACKING,
            'templateId' => $templateId,
            'filename' => $filename
        ]);
    }
}