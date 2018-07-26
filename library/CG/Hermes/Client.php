<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Message\Response as GuzzleResponse;
use SimpleXMLElement;

/**
 * Don't use this class directly, use the Client\Factory to generate a correctly configured version of this class.
 */
class Client implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'HermesClient';

    /** @var GuzzleClient */
    protected $guzzleClient;
    /** @var CourierAdapterAccount  */
    protected $account;

    public function __construct(GuzzleClient $guzzleClient, CourierAdapterAccount $account)
    {
        $this->guzzleClient = $guzzleClient;
        $this->account = $account;
    }

    public function sendRequest(RequestInterface $request)
    {
        $guzzleRequest = $this->createGuzzleRequest($request);
        $this->logInfo(str_replace('%', '%%', (string)$guzzleRequest), [], [static::LOG_CODE, 'Request']);
        try {
            $httpResponse = $guzzleRequest->send();
            $this->logInfo(str_replace('%', '%%', (string)$httpResponse), [], [static::LOG_CODE, 'Response']);
            return $this->buildResponse($request, $httpResponse);
        } catch (GuzzleCurlException|GuzzleBadResponseException $e) {
            $this->logInfo(str_replace('%', '%%', (string)$e->getResponse()), [], [static::LOG_CODE, 'Response', 'Error']);
            $this->logException($e, 'log:error', __NAMESPACE__);
            throw new StorageException('Hermes API error', $e->getCode(), $e);
        }
    }

    protected function createGuzzleRequest(RequestInterface $request)
    {
        return $this->guzzleClient->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $this->getRequestHeaders(),
            $request->asXML()
        );
    }

    protected function getRequestHeaders()
    {
        $credentials = $this->account->getCredentials();
        return [
            'Content-Type' => 'text/xml',
            'Authorization' => 'Basic ' . base64_encode($credentials['username'].':'.$credentials['password']),
        ];
    }

    protected function buildResponse(RequestInterface $request, GuzzleResponse $response)
    {
        try {
            $responseBody = $response->getBody(true);
            /** @var ResponseInterface $responseClass */
            $responseClass = $request->getResponseClass();
            return $responseClass::createFromXml(new SimpleXMLElement($responseBody));
        } catch (\Exception $e) {
            throw new StorageException('Invalid API response', $e->getCode(), $e);
        }
    }
}