<?php
namespace DataExchange\Manual;

use CG\DataExchange\Manual;
use CG\DataExchange\Manual\Mapper as ManualMapper;
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
        //todo
    }

    protected function createManualExchange(int $templateId): Manual
    {
        return $this->manualMapper->fromArray([
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'userId' => $this->activeUserContainer->getActiveUser()->getId(),
            'operation' => Manual::OPERATION_EXPORT,
            'type' => Manual::TYPE_STOCK,
            'templateId' => $templateId,
            'filename' => 'stockExport.csv',
        ]);
    }
}