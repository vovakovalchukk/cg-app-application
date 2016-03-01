<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Collection as OrderCollection;
use Generator;

interface MapperInterface
{
    /**
     * @return array|string
     */
    public function getHeaders();

    /**
     * @return Generator
     */
    public function fromOrderCollection(OrderCollection $orderCollection);
}
