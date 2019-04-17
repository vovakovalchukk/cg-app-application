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
        // Right now we only do anything different for RM. If this changes in the future this should be smarter.
        if ($shippingChannel == 'royal-mail-intersoft-ca') {
            return $this->di->get(RoyalMailApi::class);
        }
        return $this->di->get(Other::class);
    }
}