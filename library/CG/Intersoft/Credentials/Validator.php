<?php
namespace CG\Intersoft\Credentials;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Exception\OperationFailed;
use CG\Intersoft\Client\Factory as ClientFactory;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Validator implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'RoyalMailApiCredentialsValidator';

    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function __invoke(Account $account): bool
    {
        try {
            // Requesting a Client will request a token which will test the connection
            ($this->clientFactory)($account);
            return true;
        } catch (StorageException $e) {
            $this->logNotice('Could not fetch auth token for account %d, credentials presumed invalid', ['account' => $account->getId()], [static::LOG_CODE, 'Invalid']);
            return false;
        } catch (\Throwable $e) {
            $this->logException($e, 'Unknown problem fetching auth token for account %d', ['account' => $account->getId()], [static::LOG_CODE, 'Error']);
            throw new OperationFailed('Unknown problem fetching auth token', $e->getCode(), $e);
        }
    }
}