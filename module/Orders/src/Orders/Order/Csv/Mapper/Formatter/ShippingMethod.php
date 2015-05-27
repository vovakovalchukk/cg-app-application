<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Shipping\Conversion\Service as ConversionService;
use CG\OrganisationUnit\Service as OUService;
use Orders\Order\Csv\Mapper\FormatterInterface;

class ShippingMethod implements FormatterInterface
{
    protected $ouService;
    protected $conversionService;

    public function __construct(OUService $ouService, ConversionService $conversionService)
    {
        $this->setOuService($ouService)->setConversionService($conversionService);
    }

    public function __invoke(Order $order, $fieldName)
    {
        $rows = max(1, count($order->getItems()));
        $ou = $this->getOuService()->fetch($order->getOrganisationUnitId());
        $alias = $this->getConversionService()->fromMethodToAlias($order->getShippingMethod(),$ou);
        return array_fill(0, $rows, $alias ? $alias->getName() : $order->getShippingMethod());
    }

    /**
     * @return self
     */
    protected function setOuService(OUService $ouService)
    {
        $this->ouService = $ouService;
        return $this;
    }

    /**
     * @return OUService
     */
    protected function getOuService()
    {
        return $this->ouService;
    }

    /**
     * @return self
     */
    protected function setConversionService(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
        return $this;
    }

    /**
     * @return ConversionService
     */
    protected function getConversionService()
    {
        return $this->conversionService;
    }
} 
