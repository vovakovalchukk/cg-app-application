<?php
namespace CG\ShipStation;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException as GuzzleBadResponseException;
use Guzzle\Http\Exception\CurlException as GuzzleCurlException;
use Guzzle\Http\Message\Response as HttpResponse;

class Client implements LoggerAwareInterface
{
    use LogTrait;

    const API_URL = 'https://api.shipengine.com';

    /** @var  GuzzleClient */
    protected $guzzle;
    /** @var  Cryptor */
    protected $cryptor;
    /** @var  string */
    protected $partnerApiKey;

    public function __construct(GuzzleClient $guzzle, Cryptor $cryptor, string $partnerApiKey)
    {
        $this->guzzle = $guzzle;
        $this->cryptor = $cryptor;
        $this->partnerApiKey = $partnerApiKey;
    }

    public function sendRequest(RequestInterface $request, Account $account): ResponseInterface
    {
        $guzzleRequest = $this->generateHttpRequest($request, $account);
        try {
            $httpResponse = $guzzleRequest->send();
            return $this->buildResponse($request, $httpResponse);
        } catch (GuzzleCurlException|GuzzleBadResponseException $e) {
            $this->logException($e, 'log:error', __NAMESPACE__);
            throw new StorageException('ShipStation API error', $e->getCode(), $e);
        }
    }

    protected function generateHttpRequest(RequestInterface $request, Account $account)
    {
        $guzzleRequest = $this->guzzle->createRequest(strtolower($request->getMethod()))
            ->setUrl(static::API_URL . $request->getUri());
        $apiKey = $request instanceof PartnerRequestAbstract ? $this->partnerApiKey : $this->getApiKeyForAccount($account);
        $guzzleRequest
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('api-key', $apiKey);
        return $guzzleRequest;
    }

    protected function getApiKeyForAccount(Account $account)
    {
        /** @TODO: find the proper way of getting the api key for the account */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->getApiKey();
    }

    protected function buildResponse(RequestInterface $request, HttpResponse $response)
    {
        try {
            $responseBody = $response->getBody(true);
            $responseClass = $request->getResponseClass();
            $response = new $responseClass;
            if (!($response instanceof ResponseInterface)) {
                throw new \Exception('Invalid Response Class "' . $responseClass . '"');
            }
            return $response->createFromJson($responseBody);
        } catch (\Exception $e) {
            throw new StorageException('Invalid API response', $e->getCode(), $e);
        }
    }
}
