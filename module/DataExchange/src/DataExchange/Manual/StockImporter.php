<?php
namespace DataExchange\Manual;

use CG\DataExchange\Manual;
use CG\DataExchange\Manual\Mapper as ManualMapper;
use CG\DataExchange\Manual\Gearman\Workload\RunManualExchange as RunManualExchangeWorkload;
use CG\Stock\Import\File\Entity as ImportFile;
use CG\Stock\Import\File\Mapper as ImportFileMapper;
use CG\Stock\Import\File\StorageInterface as ImportFileStorage;
use CG\User\ActiveUserInterface;
use GearmanClient;

class StockImporter
{
    /** @var ManualMapper */
    protected $manualMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var ImportFileMapper */
    protected $importFileMapper;
    /** @var ImportFileStorage */
    protected $importFileStorage;
    /** @var GearmanClient */
    protected $gearmanClient;

    public function __construct(
        ManualMapper $manualMapper,
        ActiveUserInterface $activeUserContainer,
        ImportFileMapper $importFileMapper,
        ImportFileStorage $importFileStorage,
        GearmanClient $gearmanClient
    ) {
        $this->manualMapper = $manualMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->importFileMapper = $importFileMapper;
        $this->importFileStorage = $importFileStorage;
        $this->gearmanClient = $gearmanClient;
    }

    public function __invoke(int $templateId, string $action, string $fileContents): void
    {
        $importFile = $this->saveFile($action, $fileContents);
        $manualExchange = $this->createManualExchange($templateId, $action, $importFile);
        $workload = new RunManualExchangeWorkload(json_encode($manualExchange));
        $this->gearmanClient->doBackground(RunManualExchangeWorkload::buildFunctionNameForManual($manualExchange), serialize($workload));
    }

    protected function saveFile(string $action, string $fileContents): ImportFile
    {
        return $this->importFileStorage->save(
            $this->importFileMapper->fromUpload(
                $action,
                $fileContents,
                $this->activeUserContainer->getActiveUser()->getId(),
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
            )
        );
    }

    protected function createManualExchange(int $templateId, string $action, ImportFile $importFile): Manual
    {
        return $this->manualMapper->fromArray([
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'userId' => $this->activeUserContainer->getActiveUser()->getId(),
            'operation' => Manual::OPERATION_IMPORT,
            'type' => Manual::TYPE_STOCK,
            'templateId' => $templateId,
            'filename' => 'stockImport.csv',
            'options' => ['action' => $action],
            'savedFileId' => $importFile->getId(),
        ]);
    }
}