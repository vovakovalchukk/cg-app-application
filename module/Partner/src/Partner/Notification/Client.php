<?php
namespace Partner\Notification;

use CG\Partner\Entity as Partner;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Http\Message\RequestInterface as GuzzleRequest;
use Guzzle\Http\Message\Response as GuzzleResponse;
use Partner\Notification\RequestInterface as NotificationRequest;

class Client implements LoggerAwareInterface
{
    use LogTrait;

    const MAX_RETRIES = 3;

    const LOG_MESSAGE_MAX_RETRIES_EXCEEDED = 'Maximum retries has been exceeded for partner %s while performing the %s request';
    const LOG_MESSAGE_INVALID_RESPONSE = 'Invalid response while notifying partner notifying partner %s, retry number %s';
    const LOG_CODE = 'PartnerNotificationService';

    /** @var GuzzleClient */
    protected $guzzle;

    public function __construct(GuzzleClient $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function sendRequest(Partner $partner, NotificationRequest $request): GuzzleResponse
    {
        for ($retry = 0; $retry < static::MAX_RETRIES; $retry++) {
            try {
                $guzzleRequest = $this->buildHttpRequest($request);
                $this->logInfo(str_replace('%', '%%', (string) $guzzleRequest), [], [static::LOG_CODE, 'Request']);
                $response = $guzzleRequest->send();
                $this->logInfo(str_replace('%', '%%', (string) $response), [], [static::LOG_CODE, 'Response']);
                return $response;
            } catch (CurlException|BadResponseException $e) {
                $this->logWarning(static::LOG_MESSAGE_INVALID_RESPONSE , [$partner->getId(), $retry], [static::LOG_CODE]);
                $this->logInfo(str_replace('%', '%%', (string) $e->getResponse()), [], [static::LOG_CODE, 'Response']);
            }
        }

        $this->logWarning(static::LOG_MESSAGE_MAX_RETRIES_EXCEEDED, [$partner->getId(), $request->getUrl()], [static::LOG_CODE, 'MaxRetriesExceeded']);
        throw $e;
    }

    protected function buildHttpRequest(NotificationRequest $request): GuzzleRequest
    {
        return $this->guzzle->createRequest(
            $request->getMethod(),
            $request->getUrl(),
            ['Content-Type' => 'application/json'],
            json_encode($request->toArray())
        );
    }
}
