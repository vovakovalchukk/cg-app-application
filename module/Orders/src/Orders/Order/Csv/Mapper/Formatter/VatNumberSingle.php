<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Client\Gearman\Generator\SetPrintedDate as PrintedDateGenerator;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Tax\Service as TaxService;
use Orders\Order\Csv\Mapper\FormatterInterface;

class VatNumberSingle implements FormatterInterface
{
    /** @var TaxService */
    protected $taxService;
    /** @var PrintedDateGenerator */
    protected $printedDateGenerator;

    public function __construct(TaxService $taxService, PrintedDateGenerator $printedDateGenerator)
    {
        $this->setTaxService($taxService)
            ->setPrintedDateGenerator($printedDateGenerator);
    }

    public function __invoke(Order $order, $fieldName)
    {
        if (!$order->getVatNumber()) {
            $this->determineAndStoreOrderVatNumber($order);
        }
        return $order->getVatNumber();
    }

    protected function determineAndStoreOrderVatNumber(Order $order)
    {
        $order->setVatNumber($this->taxService->getVatNumberForOrder($order));
        $this->printedDateGenerator->createJob($order);
    }

    protected function setTaxService(TaxService $taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }

    protected function setPrintedDateGenerator(PrintedDateGenerator $printedDateGenerator)
    {
        $this->printedDateGenerator = $printedDateGenerator;
        return $this;
    }
}
