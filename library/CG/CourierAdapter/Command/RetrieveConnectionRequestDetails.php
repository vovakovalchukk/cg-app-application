<?php
namespace CG\CourierAdapter\Command;

use CG\CourierAdapter\Exception\NotFound;
use CG\CourierAdapter\StorageInterface as Storage;
use Symfony\Component\Console\Output\OutputInterface;

class RetrieveConnectionRequestDetails
{
    const ACCOUNT_CONNECTION_REQUEST_KEY_TEMPLATE = '%s-%s-accountConnectionData';

    /** @var Storage */
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function __invoke(int $ouId, int $shippingAccountId, OutputInterface $output)
    {
        try {
            $storedDetails = json_decode($this->storage->get($this->getStorageKey($ouId, $shippingAccountId)));
            $output->writeln('Stored details for OU %s with shippingAccount %s are as follows:');
            $output->writeln(print_r($storedDetails));
        } catch (\Throwable $exception) {
            $output->writeln(sprintf('Unable to retrieve stored details for OU %s with shippingAccount %s', $ouId, $shippingAccountId));
        }
    }

    protected function getStorageKey(int $ouId, int $shippingAccountId): string
    {
        return sprintf(static::ACCOUNT_CONNECTION_REQUEST_KEY_TEMPLATE, $ouId, $shippingAccountId);
    }
}