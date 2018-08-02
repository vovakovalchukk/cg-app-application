<?php
namespace CG\ShipStation\ShippingService;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use function CG\Stdlib\hyphenToClassname;
use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(Account $account): ShippingServiceInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($account->getChannel());
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className, ['account' => $account]);
        if (!$class instanceof ShippingServiceInterface) {
            throw new \RuntimeException($className . ' does not implement ' . ShippingServiceInterface::class);
        }
        return $class;
    }

    protected function getClassNameForChannel(string $channel)
    {
        return hyphenToClassname(preg_replace('/-ss$/', '', $channel));
    }
}