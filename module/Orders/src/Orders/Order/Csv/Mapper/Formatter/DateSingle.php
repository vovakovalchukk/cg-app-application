<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG_UI\View\Helper\DateFormat;
use Orders\Order\Csv\Mapper\FormatterInterface;

class DateSingle implements FormatterInterface
{
    /** @var DateFormat */
    protected $dateFormatHelper;

    public function __construct(DateFormat $dateFormatHelper)
    {
        $this->setDateFormatHelper($dateFormatHelper);
    }

    public function __invoke(Order $order, $fieldName)
    {
        $dateFormatter = $this->dateFormatHelper;
        $getter = 'get' . ucfirst($fieldName);
        $date = $order->$getter();
        return $dateFormatter($date, StdlibDateTime::FORMAT);
    }

    protected function setDateFormatHelper(DateFormat $dateFormatHelper)
    {
        $this->dateFormatHelper = $dateFormatHelper;
        return $this;
    }
}
