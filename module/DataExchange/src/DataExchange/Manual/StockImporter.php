<?php
namespace DataExchange\Manual;

use CG\DataExchange\Manual;
use CG\DataExchange\Manual\Mapper as ManualMapper;
use CG\User\ActiveUserInterface;

class StockImporter
{
    /** @var ManualMapper */
    protected $manualMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(ManualMapper $manualMapper, ActiveUserInterface $activeUserContainer)
    {
        $this->manualMapper = $manualMapper;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function __invoke(int $templateId, string $action, string $fileContents): void
    {
        $manualExchange = $this->createManualExchange();
        // todo: store file contents
        // create job for new worker that takes manualExchange and some identifier for the saved file
    }

    protected function createManualExchange(int $templateId, string $action): Manual
    {
        return $this->manualMapper->fromArray([
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'operation' => Manual::OPERATION_IMPORT,
            'type' => Manual::TYPE_STOCK,
            'templateId' => $templateId,
            'filename' => 'stockImport.csv',
            'options' => ['action' => $action],
        ]);
    }
}