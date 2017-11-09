<?php
namespace Orders\Order\Csv\Mapper;

trait ConvertToOrderIdsFlagTrait
{
    protected $convertToOrderIdsFlag = true;

    /**
     * @param bool $convertToOrderIdsFlag
     * @return $this
     */
    public function setConvertToOrderIdsFlag(bool $convertToOrderIdsFlag)
    {
        $this->convertToOrderIdsFlag = $convertToOrderIdsFlag;
        return $this;
    }
}