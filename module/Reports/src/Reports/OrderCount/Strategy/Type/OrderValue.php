<?php
namespace Reports\OrderCount\Strategy\Type;

use CG\ExchangeRate\Service as ExchangeService;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\Date;

class OrderValue implements TypeInterface
{
    protected $exchangeService;

    public function __construct(ExchangeService $service)
    {
        $this->exchangeService = $service;
    }

    public function getIncreaseValue(Order $order)
    {
        if ($order->getCurrencyCode() == ExchangeService::DEFAULT_BASE_CURRENCY_CODE) {
            return (int) $order->getTotal();
        }

        return $this->exchangeService->convertAmount(
            $order->getCurrencyCode(),
            ExchangeService::DEFAULT_BASE_CURRENCY_CODE,
            $order->getTotal(),
            new Date(explode(' ', $order->getPurchaseDate())[0])
        );
    }
}
