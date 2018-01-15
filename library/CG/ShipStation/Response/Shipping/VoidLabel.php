<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\ResponseAbstract;

class VoidLabel extends ResponseAbstract
{
    /** @var bool */
    protected $approved;
    /** @var string */
    protected $message;

    public function __construct(bool $approved, string $message)
    {
        $this->approved = $approved;
        $this->message = $message;
    }

    protected static function build($decodedJson)
    {
        return new static($decodedJson->approved, $decodedJson->message);
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}