<?php
namespace ShipStation\Account\ChannelSpecificVariables;

use CG\Account\Client\Entity as Account;
use CG\ShipStation\GetClassNameForChannelTrait;
use ShipStation\Account\ChannelSpecificVariablesInterface;
use Zend\Di\Di;

class Factory
{
    use GetClassNameForChannelTrait;

    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(Account $account): ChannelSpecificVariablesInterface
    {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($account->getChannel());
        if (!class_exists($className)) {
            $className = Other::class;
        }

        $class = $this->di->get($className, ['account' => $account]);
        if (!$class instanceof ChannelSpecificVariablesInterface) {
            throw new \InvalidArgumentException($className . ' is not an instance of ' . ChannelSpecificVariablesInterface::class);
        }
        return $class;
    }
}
