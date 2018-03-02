<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\ServicesInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    public function getShippingServiceForAccount(Account $account): ServicesInterface
    {
        return $this->getClassForAccount($account, ServicesInterface::class, 'ShippingService');
    }

    protected function getClassForAccount(Account $account, string $interface, string $className, ...$arguments)
    {
        $class = __NAMESPACE__ . '\\' . hyphenToClassname($account->getChannel()) . '\\' . $className;
        if (!class_exists($class) || !is_a($class, $interface, true)) {
            throw new NotFound(sprintf('Unsupported account "%s"', $account->getChannel()));
        }
        return new $class(...$arguments);
    }
}