<?php
namespace CG\UkMail;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;
use GuzzleHttp\Message\RequestInterface as GuzzleRequest;
use CG\UkMail\Response\ResponseInterface;
use CG\UkMail\Request\RequestInterface;
use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\AbstractSoapResponse;

class Client implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'UkMailClient';

    /** @var GuzzleClient */
    protected $guzzleClient;
    /** @var CourierAdapterAccount  */
    protected $account;

    public function __construct(GuzzleClient $guzzleClient, CourierAdapterAccount $account)
    {
        $this->guzzleClient = $guzzleClient;
        $this->account = $account;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $guzzleRequest = $this->createGuzzleRequest($request);
        $this->logInfo(str_replace('%', '%%', (string)$guzzleRequest), [], [static::LOG_CODE, 'Request']);
        try {
            $httpResponse = $this->guzzleClient->send($guzzleRequest);
            $this->logInfo(str_replace('%', '%%', (string)$httpResponse), [], [static::LOG_CODE, 'Response']);
            return $this->buildResponse($request, $httpResponse);
        } catch (GuzzleBadResponseException $e) {
            $this->logInfo(str_replace('%', '%%', (string)$e->getResponse()), [], [static::LOG_CODE, 'Response', 'Error']);
            $this->logException($e, 'log:error', __NAMESPACE__);
            throw new StorageException('Hermes API error', $e->getCode(), $e);
        }
    }

    protected function createGuzzleRequest(RequestInterface $request): GuzzleRequest
    {
        return $this->guzzleClient->createRequest(
            $request->getMethod(),
            $request->getUri(),
            [
                'headers' => $this->getRequestHeaders(),
                'body' => $request->asXML()
            ]
        );
    }

    protected function getRequestHeaders(): array
    {
        $credentials = $this->account->getCredentials();
        return [
            'Content-Type' => 'text/xml',
            'Authorization' => 'Basic ' . base64_encode($credentials['username'].':'.$credentials['password']),
        ];
    }

    protected function buildResponse(RequestInterface $request, GuzzleResponse $response): ResponseInterface
    {
        try {
            /** @var ResponseInterface $responseClass */
            $responseClass = $request->getResponseClass();

            if ($responseClass instanceof AbstractRestResponse) {
                $responseBody = $response->json();
                return $responseClass::createResponse($responseBody);
            }

            if ($responseClass instanceof AbstractSoapResponse) {
                $responseBody = $response->getBody(true);
                return $responseClass::createResponse($responseBody);
            }

            throw new \RuntimeException('Incorrect Response Class '.get_class($responseClass));
        } catch (\Exception $e) {
            throw new StorageException('Invalid API response', $e->getCode(), $e);
        }
    }
}