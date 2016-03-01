<?php
namespace Orders\Order\Csv;

use CG\Order\Service\Filter as OrderFilter;
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
    public function fromOrderFilter(OrderFilter $orderFilter);

    /**
     * @return Generator
     */
    public function fromOrderCollection(OrderCollection $orderCollection);
}
