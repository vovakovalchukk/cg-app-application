<?php
namespace DataExchange\Manual;

use CG\DataExchange\Manual;
use CG\DataExchange\Manual\Mapper as ManualMapper;
use CG\DataExchange\Manual\Gearman\Workload\RunManualExchange as RunManualExchangeWorkload;
use CG\DataExchange\Runner\Context;
use CG\DataExchange\RunnerInterface;
use CG\DataExchange\Runner\StockExport\Builder as StockExportBuilder;
use CG\User\ActiveUserInterface;
use GearmanClient;

class StockExporter
{
    /** @var ManualMapper */
    protected $manualMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var RunnerInterface */
    protected $stockExporter;

    public function __construct(
        ManualMapper $manualMapper,
        ActiveUserInterface $activeUserContainer,
        GearmanClient $gearmanClient,
        StockExportBuilder $stockExportBuilder
    ) {
        $this->manualMapper = $manualMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->gearmanClient = $gearmanClient;
        $this->stockExporter = ($stockExportBuilder)();
    }

    public function download(int $templateId): string
    {
        $manual = $this->createManualExchange($templateId);
        $context = new Context();
        $context = ($this->stockExporter)($manual, $context);
        return $context->getFileContents();
    }

    public function sendViaEmail(int $templateId): void
    {
        $manual = $this->createManualExchange($templateId, ['toDataExchangeAccountType' => 'user']);
        $workload = new RunManualExchangeWorkload(json_encode($manual));
        $this->gearmanClient->doBackground(RunManualExchangeWorkload::buildFunctionNameForManual($manual), serialize($workload));
    }

    protected function createManualExchange(int $templateId, array $additional = []): Manual
    {
        $array = [
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'userId' => $this->activeUserContainer->getActiveUser()->getId(),
            'operation' => Manual::OPERATION_EXPORT,
            'type' => Manual::TYPE_STOCK,
            'templateId' => $templateId,
            'filename' => 'stockExport.csv',
        ];
        return $this->manualMapper->fromArray(array_merge($array, $additional));
    }
}