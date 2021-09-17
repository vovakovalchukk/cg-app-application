<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;

class Collection extends AbstractRestResponse implements ResponseInterface
{
    /** @var string */
    protected $bookingMessage;
    /** @var string */
    protected $collectionJobNumber;

    public function __construct(string $bookingMessage, string $collectionJobNumber)
    {
        $this->bookingMessage = $bookingMessage;
        $this->collectionJobNumber = $collectionJobNumber;
    }

    public static function createResponse($response): ResponseInterface
    {
        $bookingMessage = $response['bookingMessage'];
        $collectionJobNumber = $response['collectionJobNumber'];

        return new static($bookingMessage, $collectionJobNumber);
    }

    public function getBookingMessage(): string
    {
        return $this->bookingMessage;
    }

    public function getCollectionJobNumber(): string
    {
        return $this->collectionJobNumber;
    }
}