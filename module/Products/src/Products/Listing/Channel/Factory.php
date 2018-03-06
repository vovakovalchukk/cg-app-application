<?php
namespace Products\Listing\Channel;

use CG\Account\Shared\Entity as Account;
use CG\Di\Di;
use Products\Listing\Exception as ListingException;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function buildChannelService(Account $account, array $postData = [])
    {
        $channelServiceName = __NAMESPACE__ . '\\' . hyphenToClassname($account->getChannel()) . '\\' . 'Service';
        if (!class_exists($channelServiceName)) {
            throw new \InvalidArgumentException('The class: ' . $channelServiceName . ' could not be found for the provided channel: ' . $account->getChannel());
        }
        $params = !empty($postData) ? ['postData' => $postData] : [];
        return $this->di->newInstance($channelServiceName, $params);
    }

    public function fetchAndValidateChannelService(Account $account, string $className, array $postData = [])
    {
        try {
            $service = $this->buildChannelService($account, $postData);
            if ($service instanceof $className) {
                return $service;
            }
            throw new ListingException('The account with ID ' . $account->getId() . ' does not support this action.');
        } catch (\InvalidArgumentException $e) {
            throw new ListingException('The account with ID ' . $account->getId() . ' is not valid');
        }
    }
}
