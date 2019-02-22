<?php
namespace Orders\Order\Filter;

use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Batch\Service as BatchService;
use CG\Order\Shared\Batch\Mapper as BatchMapper;
use CG_UI\View\Filters\SelectOptionsInterface;

class Batch implements SelectOptionsInterface
{
    protected $batchService;
    protected $batchMapper;
    
    public function __construct(BatchService $batchService, BatchMapper $batchMapper)
    {
        $this->setBatchService($batchService)
            ->setBatchMapper($batchMapper);
    }

    public function getBatches()
    {
        return $this->getBatchService()->getBatches();
    }

    protected function getBatchService()
    {
        return $this->batchService;
    }

    protected function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    protected function getBatchMapper()
    {
        return $this->batchMapper;
    }

    protected function setBatchMapper(BatchMapper $batchMapper)
    {
        $this->batchMapper = $batchMapper;
        return $this;
    }

    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        $options = [];
        try {
            $batches = $this->getBatches();
            foreach ($batches as $batchArray) {
                $batch = $this->getBatchMapper()->fromArray($batchArray);
                $options[$batch->getName()] = $batch->getName();
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
} 