<?php
namespace CG\ShipStation\Messages;

class CurrencyAmount
{
    /** @var float */
    protected $amount;
    /** @var string */
    protected $currency;

    public function __construct(float $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function build(\stdClass $decodedJson): CurrencyAmount
    {
        return new static($decodedJson->amount, $decodedJson->currency);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}