<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Collection as OrderCollection;

interface MapperInterface
{
    public function getHeaders();
    public function fromOrderCollection(OrderCollection $orderCollection);
}
