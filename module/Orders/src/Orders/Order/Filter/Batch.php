<?php
namespace Orders\Order\Filter;

use CG\Stdlib\Exception\Runtime\NotFound;

class Batch extends Channel
{
    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        $options = [];
        try {
            $batches = $this->getBatches();
            foreach ($batches as $batch) {
                $options[$batch->getId()] = $batch->getName();
            }
        } catch (NotFound $exception) {
            // No accounts means no channels so ignore
        }
        return $options;
    }
} 