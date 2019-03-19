<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Http\StatusCode as HttpStatusCode;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\RoyalMailApi\Client\AuthToken;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\RequestInterface;
use CG\RoyalMailApi\Response\Factory as ResponseFactory;
use CG\RoyalMailApi\ResponseInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Message\Response as GuzzleResponse;
use http\Exception\UnexpectedValueException;

/**
 * Don't use this class directly, use the Client\Factory to generate a correctly configured version of this class.
 */
class Client implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'RoyalMailApiClient';
    const LOG_REQUEST_MSG = '%s request for uri %s';

    /** @var GuzzleClient */
    protected $guzzleClient;
    /** @var ResponseFactory */
    protected $responseFactory;
    /** @var CourierAdapterAccount */
    protected $account;
    /** @var Credentials */
    protected $credentials;
    /** @var ?AuthToken */
    protected $authToken;

    public function __construct(
        GuzzleClient $guzzleClient,
        ResponseFactory $responseFactory,
        CourierAdapterAccount $account,
        Credentials $credentials,
        ?AuthToken $authToken = null
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->responseFactory = $responseFactory;
        $this->account = $account;
        $this->credentials = $credentials;
        $this->authToken = $authToken;
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        $guzzleRequest = $this->guzzleClient->createRequest($request->getMethod(), $request->getUri(), [
            'headers' => $this->getRequestHeaders($request),
            'body' => $this->getRequestBody($request),
        ]);
        try {
            $guzzleResponse = $this->guzzleClient->send($guzzleRequest);
            $json = $guzzleResponse->json(['object' => true]);
            return ($this->responseFactory)($request, $json);
        } catch (GuzzleRequestException $exception) {
            $guzzleResponse = $exception->getResponse();
            if ($guzzleResponse && $guzzleResponse->getStatusCode() == HttpStatusCode::NOT_FOUND) {
                throw new NotFound($exception->getMessage(), HttpStatusCode::NOT_FOUND, $exception);
            }
            $this->logWarningException($exception, 'Royal Mail API error', [], [static::LOG_CODE, 'Exception', get_class($exception)]);
            throw new StorageException('There was a problem contacting Royal Mail', $exception->getCode(), $exception);
        } catch (\Throwable $throwable) {
            $this->logWarningException($throwable, 'Royal Mail API error', [], [static::LOG_CODE, 'Throwable', get_class($throwable)]);
            throw new StorageException('There was a problem contacting Royal Mail', $throwable->getCode());
        } finally {
            $this->logDebug(static::LOG_REQUEST_MSG, [$request->getMethod(), $request->getUri()], array_filter([static::LOG_CODE, 'Request', isset($guzzleResponse) ? $guzzleResponse->getStatusCode() : null]), ['request' => (string) $guzzleRequest, 'response' => (string) ($guzzleResponse ?? '-')]);
        }
    }

    protected function getRequestHeaders(RequestInterface $request): array
    {
        $headers = [
            'x-ibm-client-id' => $this->credentials->getClientId(),
            'x-ibm-client-secret' => $this->credentials->getClientSecret(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if ($this->getAuthToken()) {
            $headers['x-rmg-auth-token'] = $this->getAuthToken()->getToken();
        }
        return array_merge($headers, $request->getAdditionalHeaders($this->account, $this->credentials));
    }

    protected function getRequestBody(RequestInterface $request): ?string
    {
        $jsonData = $request->jsonSerialize();
        if (empty($json)) {
            return null;
        }
        $json = json_encode($jsonData);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Request of type ' . get_class($request) . ' could not be encoded as JSON: ' . json_last_error_msg());
        }
        return $json;
    }

    public function setAuthToken(AuthToken $authToken): self
    {
        $this->authToken = $authToken;
        return $this;
    }

    public function getAuthToken(): ?AuthToken
    {
        return $this->authToken;
    }
}