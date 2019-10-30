<?php
namespace DataExchange\Manual;

use CG\DataExchange\Manual;
use CG\DataExchange\Manual\Mapper as ManualMapper;
use CG\DataExchange\Manual\Gearman\Workload\RunManualExchange as RunManualExchangeWorkload;
use CG\DataExchange\Runner\Context;
use CG\DataExchange\RunnerInterface;
use CG\DataExchange\Runner\OrderExport\Builder as OrderExportBuilder;
use CG\User\ActiveUserInterface;
use GearmanClient;

class OrderExporter
{
    /** @var ManualMapper */
    protected $manualMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var RunnerInterface */
    protected $orderExporter;

    public function __construct(
        ManualMapper $manualMapper,
        ActiveUserInterface $activeUserContainer,
        GearmanClient $gearmanClient,
        OrderExportBuilder $orderExportBuilder
    ) {
        $this->manualMapper = $manualMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->gearmanClient = $gearmanClient;
        $this->orderExporter = ($orderExportBuilder)();
    }

    public function download(int $templateId, string $savedFilterName): string
    {
        $manual = $this->createManualExchange($templateId, $savedFilterName);
        $context = new Context();
        $context = ($this->orderExporter)($manual, $context);
        return $context->getFileContents();
    }

    public function sendViaEmail(int $templateId, string $savedFilterName): void
    {
        $manual = $this->createManualExchange($templateId, $savedFilterName, ['toDataExchangeAccountType' => 'user']);
        $workload = new RunManualExchangeWorkload(json_encode($manual));
        $this->gearmanClient->doBackground(RunManualExchangeWorkload::buildFunctionNameForManual($manual), serialize($workload));
    }

    protected function createManualExchange(int $templateId, string $savedFilterName, array $additional = []): Manual
    {
        $userId = $this->activeUserContainer->getActiveUser()->getId();
        $array = [
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'userId' => $this->activeUserContainer->getActiveUser()->getId(),
            'operation' => Manual::OPERATION_EXPORT,
            'type' => Manual::TYPE_ORDER,
            'templateId' => $templateId,
            'filename' => 'orderExport.csv',
            'options' => ['savedFilterName' => $savedFilterName, 'savedFilterUserId' => $userId],
        ];
        return $this->manualMapper->fromArray(array_merge($array, $additional));
    }
}