<?php
namespace Orders\Order\Filter\Marketplace\Channel;

use CG\Account\Shared\Entity as Account;
use Orders\Order\Filter\Marketplace\ChannelInterface;
use Zend\Di\Di;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(Account $account): ChannelInterface
    {
        $className = __NAMESPACE__ . '\\' . hyphenToClassname($account->getChannel());
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('No class exists for channel ' . $account->getChannel());
        }
        $class = $this->di->get($className);
        if (!$class instanceof ChannelInterface) {
            throw new \InvalidArgumentException($className . ' does not implement ' . ChannelInterface::class);
        }
        return $class;
    }
}