<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Collection as OrderCollection;

interface FormatterInterface
{
    public function __invoke(OrderCollection $orders);
}
