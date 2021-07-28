<?php
namespace CG\UkMail;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\RequestException as ClientRequestException;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;
use GuzzleHttp\Message\RequestInterface as GuzzleRequest;
use CG\UkMail\Response\ResponseInterface;
use CG\UkMail\Request\RequestInterface;
use CG\UkMail\Request\Soap\RequestInterface as SoapRequestInterface;

class Client implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'UkMailClient';
    protected const LOG_MSG_REQUEST = 'Sent %s request to %s';

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
        try {
            $httpResponse = $this->guzzleClient->send($guzzleRequest);
            $this->logRequest($guzzleRequest, $httpResponse);
            return $this->buildResponse($request, $httpResponse);
        } catch (GuzzleBadResponseException $exception) {
            $this->logRequest($guzzleRequest, ($exception instanceof ClientRequestException ? $exception->getResponse() : null));
            $this->logException($exception, 'log:error', __NAMESPACE__);

            $error = $this->handleErrorMessages($exception, $request);

            throw new StorageException("UK Mail API error ".$error, $exception->getCode(), $exception);
        }
    }

    protected function handleErrorMessages(GuzzleBadResponseException $exception, RequestInterface $request): string
    {
        if ($request instanceof SoapRequestInterface) {
            $errorMessages = $exception->getResponse()->getBody();
            return $this->handleXmlErrorMessages($errorMessages);
        }

        $errorMessages = $exception->getResponse()->json();
        return $this->handleJsonErrorMessages($errorMessages);
    }

    protected function handleJsonErrorMessages(array $errorMessages): string
    {
        $error = '';
        foreach ($errorMessages as $errorFieldName => $errorMessage) {
            if (is_array($errorMessage)) {
                $subError = $this->handleJsonErrorMessages($errorMessage);
                $error .= ucfirst($errorFieldName) . ":  ".$subError . "  ";
                continue;
            }
            $error .= ucfirst($errorFieldName) . ": " . $errorMessage . "  ";
        }

        return $error;
    }

    protected function handleXmlErrorMessages(string $errorMessages): string
    {
        $error = '';
        if ($errorMessages != '') {
            $error = $errorMessages;
        }
        return $error;
    }

    protected function logRequest(GuzzleRequest $request, GuzzleResponse $response = null)
    {
        $this->logDebug(static::LOG_MSG_REQUEST, [$request->getMethod(), $request->getUrl()], array_filter([static::LOG_CODE, $response !== null ? $response->getStatusCode() : null]), ['request' => (string) $request, 'response' => ($response !== null ? (string) $response : null)]);
    }

    protected function createGuzzleRequest(RequestInterface $request): GuzzleRequest
    {
        return $this->guzzleClient->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getOptions()
//            [
//                'headers' => $this->getRequestHeaders(),
//                'body' => $request->asXML()
//            ]
        );
    }

//    protected function getRequestHeaders(RequestInterface $request): array
//    {
//        $defaultOptions = [
//            'headers' => ['Accept' => 'application/json'],
//        ];
//
//        $credentials = $this->account->getCredentials();
//        return [
//            'Content-Type' => 'text/xml',
//            'Authorization' => 'Basic ' . base64_encode($credentials['username'].':'.$credentials['password']),
//        ];
//    }

    protected function buildResponse(RequestInterface $request, GuzzleResponse $response): ResponseInterface
    {
        try {
            /** @var ResponseInterface $responseClass */
            $responseClass = $request->getResponseClass();
            if ($responseClass::isRestResponse()) {
                $responseBody = $response->json();
                return $responseClass::createResponse($responseBody);
            }

            $responseBody = $response->getBody(true);
            return $responseClass::createResponse($responseBody);
        } catch (\Exception $exception) {
            throw new StorageException('Invalid API response', $exception->getCode(), $exception);
        }
    }
}