<?php
namespace Orders\Order\Csv\Formatters;

class TotalOrderDiscountSingle extends TotalOrderDiscount
{
    public function __construct()
    {
        parent::__construct(false);
    }
}