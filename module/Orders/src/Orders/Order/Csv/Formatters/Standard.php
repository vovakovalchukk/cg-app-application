<?php
namespace Orders\Order\Csv\Formatters;

use CG\Order\Shared\Collection as OrderCollection;
use Orders\Order\Csv\FormatterInterface;

class Standard implements FormatterInterface
{
    protected $fieldNames;

    public function __construct(array $fieldNames)
    {
        $this->fieldNames = $fieldNames;
    }

    public function __invoke(OrderCollection $orders)
    {
        $columns = [];

        foreach($orders as $order) {
            if($order->getItems()->count() === 0) {
                foreach ($this->fieldNames as $header => $fieldName) {
                    $getter = 'get' . ucfirst($fieldName);
                    try {
                        $columns[$header][] = $order->$getter();
                    } catch (\BadMethodCallException $e) {
                        $columns[$header][] = '';
                    }
                    continue;
                }
            }

            foreach($order->getItems() as $item) {
                foreach($this->fieldNames as $header => $fieldName) {
                    $getter = 'get' . ucfirst($fieldName);
                    try {
                        $columns[$header][] = $order->$getter();
                    } catch (\BadMethodCallException $e) {
                        if(is_callable([$item, $getter])) {
                            $columns[$header][] = $item->$getter();
                        } else {
                            $columns[$header][] = '';
                        }
                    }
                }

            }
        }

        return $columns;
    }
}
