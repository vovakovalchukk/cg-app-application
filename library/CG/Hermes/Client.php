<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use Guzzle\Http\Client as GuzzleClient;
use SimpleXMLElement;

class Client
{
    const LOG_CODE = 'HermesClient';

    /** @var GuzzleClient */
    protected $guzzleClient;

    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    public function sendRequest(RequestInterface $request, CourierAdapterAccount $account)
    {
        $guzzleRequest = $this->createGuzzleRequest($request, $account);
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

    protected function createGuzzleRequest(RequestInterface $request, CourierAdapterAccount $account)
    {
        return $this->guzzle->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $this->getRequestHeaders($account),
            $request->asXML()
        );
    }

    protected function getRequestHeaders(CourierAdapterAccount $account)
    {
        $credentials = $account->getCredentials();
        return [
            'Content-Type' => 'text/xml',
            'Authorization' => 'Basic ' . base64_encode($credentials['username'].':'.$credentials['password']),
        ];
    }

    protected function buildResponse(RequestInterface $request, HttpResponse $response)
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