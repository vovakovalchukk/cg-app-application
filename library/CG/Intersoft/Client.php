<?php
namespace CG\Intersoft;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Http\StatusCode as HttpStatusCode;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Intersoft\Credentials;
use CG\Intersoft\RequestInterface;
use CG\Intersoft\Response\Factory as ResponseFactory;
use CG\Intersoft\ResponseInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;

/**
 * Don't use this class directly, use the Client\Factory to generate a correctly configured version of this class.
 */
class Client implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'IntersoftApiClient';
    const LOG_REQUEST_MSG = '%s request for uri %s';

    /** @var GuzzleClient */
    protected $guzzleClient;
    /** @var ResponseFactory */
    protected $responseFactory;
    /** @var CourierAdapterAccount */
    protected $account;
    /** @var Credentials */
    protected $credentials;

    public function __construct(
        GuzzleClient $guzzleClient,
        ResponseFactory $responseFactory,
        CourierAdapterAccount $account,
        Credentials $credentials
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->responseFactory = $responseFactory;
        $this->account = $account;
        $this->credentials = $credentials;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        $guzzleRequest = $this->guzzleClient->createRequest($request->getMethod(), $request->getUri(), [
            'body' => $this->getRequestBody($request),
        ]);
        try {
            $guzzleResponse = $this->guzzleClient->send($guzzleRequest);
            $xml = $guzzleResponse->xml();
            return ($this->responseFactory)($request, $xml);
        } catch (GuzzleRequestException $exception) {
            $guzzleResponse = $exception->getResponse();
            if ($guzzleResponse && $guzzleResponse->getStatusCode() == HttpStatusCode::NOT_FOUND) {
                throw new NotFound($exception->getMessage(), HttpStatusCode::NOT_FOUND, $exception);
            }
            $this->logWarningException($exception, 'Intersoft API error', [], [static::LOG_CODE, 'Exception', get_class($exception)]);
            throw new StorageException('There was a problem contacting Intersoft', $exception->getCode(), $exception);
        } catch (\Throwable $throwable) {
            $this->logWarningException($throwable, 'Intersoft API error', [], [static::LOG_CODE, 'Throwable', get_class($throwable)]);
            throw new StorageException('There was a problem contacting Intersoft', $throwable->getCode());
        } finally {
            $this->logDebug(static::LOG_REQUEST_MSG, [$request->getMethod(), $request->getUri()], array_filter([static::LOG_CODE, 'Request', isset($guzzleResponse) ? $guzzleResponse->getStatusCode() : null]), ['request' => (string) $guzzleRequest, 'response' => (string) ($guzzleResponse ?? '-')]);
        }
    }

    protected function getRequestBody(RequestInterface $request): ?string
    {
        return $request->asXml();
    }
}