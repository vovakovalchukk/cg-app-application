<?php
namespace CG\ShipStation;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\ShipStation\Credentials;
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
        $apiKey = $request instanceof PartnerRequestAbstract ? $this->partnerApiKey : $this->getApiKeyForAccount($account);
        return $this->guzzle->createRequest(
            $request->getMethod(),
            $request->getUri(),
            ['Content-Type' => 'application/json', 'api-key' => $apiKey],
            $request->toJson()
        );
    }

    protected function getApiKeyForAccount(Account $account)
    {
        /** @var Credentials $credentials */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        return $credentials->getApiKey();
    }

    protected function buildResponse(RequestInterface $request, HttpResponse $response)
    {
        try {
            $responseBody = $response->getBody(true);
            /** @var ResponseInterface $responseClass */
            $responseClass = $request->getResponseClass();
            return $responseClass::createFromJson($responseBody);
        } catch (\Exception $e) {
            throw new StorageException('Invalid API response', $e->getCode(), $e);
        }
    }
}
