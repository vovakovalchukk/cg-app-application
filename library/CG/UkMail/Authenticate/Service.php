<?php
namespace CG\UkMail\Authenticate;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\CourierAdapter\Exception\UserError;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Client\Factory as ClientFactory;
use CG\UkMail\Request\Rest\Authenticate as AuthenticateRequest;
use CG\UkMail\Response\Rest\Authenticate as AuthenticateResponse;
use Predis\Client as PredisClient;

class Service implements LoggerAwareInterface
{
    protected const TOKEN_KEY = 'CGUkMailAuthenticationToken::%d';
    protected const TOKEN_TTL = 900; //15 minutes

    protected const LOG_CODE = 'UkMailAuthenticateService';
    protected const LOG_FETCHING_TOKEN_MSG = 'Fetching UK Mail token %s for account %d from redis';
    protected const LOG_FETCHING_TOKEN_API_MSG = 'Fetching UK Mail token for account %d from UK Mail API';
    protected const LOG_SAVING_TOKEN_MSG = 'Saving UK Mail token %s for account %d to redis';

    use LogTrait;

    /** @var PredisClient */
    protected $predisClient;
    /** @var ClientFactory */
    protected $clientFactory;

    protected $tokens = [];

    public function __construct(PredisClient $predisClient, ClientFactory $clientFactory)
    {
        $this->predisClient = $predisClient;
        $this->clientFactory = $clientFactory;
    }

    public function getAuthenticationToken(CourierAdapterAccount $account): string
    {
        if (isset($this->tokens[$account->getId()])) {
            return $this->tokens[$account->getId()];
        }

        if (($token = $this->fetchToken($account)) != null) {
            $this->logDebug(static::LOG_FETCHING_TOKEN_MSG, [$token, $account->getId()], static::LOG_CODE);
            $this->tokens[$account->getId()] = $token;
            return $token;
        }

        $authResponse = $this->authenticate($account);

        $this->saveToken($account, $authResponse->getAuthenticationToken());
        $this->tokens[$account->getId()] = $authResponse->getAuthenticationToken();

        return $authResponse->getAuthenticationToken();
    }

    protected function authenticate(CourierAdapterAccount $account): AuthenticateResponse
    {
        $this->logDebug(static::LOG_FETCHING_TOKEN_API_MSG, [$account->getId()], static::LOG_CODE);
        $authRequest = $this->createAuthenticateRequest($account);
        try {
            $client = ($this->clientFactory)($account, $authRequest);
            return $client->sendRequest($authRequest);
        } catch (\Exception $exception) {
            throw new UserError($exception->getMessage());
        }
    }

    protected function createAuthenticateRequest(CourierAdapterAccount $account): AuthenticateRequest
    {
        return new AuthenticateRequest(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $account->getCredentials()['password']
        );
    }

    protected function fetchToken(CourierAdapterAccount $account): ?string
    {
        return $this->predisClient->get($this->getKey($account));
    }

    protected function saveToken(CourierAdapterAccount $account, string $token): void
    {
        $this->logDebug(static::LOG_SAVING_TOKEN_MSG, [$token, $account->getId()], static::LOG_CODE);
        $this->predisClient->setex(
            $this->getKey($account),
            static::TOKEN_TTL,
            $token
        );
    }

    protected function getKey(CourierAdapterAccount $account): string
    {
        return sprintf(static::TOKEN_KEY, $account->getId());
    }
}