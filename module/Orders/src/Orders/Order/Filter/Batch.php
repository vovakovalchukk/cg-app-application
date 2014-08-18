<?php
namespace Orders\Order\Filter;

use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Batch\Service as BatchService;
use CG\Order\Shared\Batch\Mapper as BatchMapper;

class Batch extends Channel
{
    protected $batchService;
    protected $batchMapper;
    
    public function __construct(BatchService $batchService)
    {
        $this->setBatchService($batchService);
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
                $options[$batch->getId()] = $batch->getName();/*[
                    'name' => "this",
                    'filter' => json_encode(
                        [
                            'batch' => [
                                $batch->getId()
                            ]
                        ]
                    )
                ];*/
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
} 