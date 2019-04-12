<?php
namespace CourierAdapter\Form;

use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(string $shippingChannel)
    {
        return $this->di->get(Other::class);
    }
}