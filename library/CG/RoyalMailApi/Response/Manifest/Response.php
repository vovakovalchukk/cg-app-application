<?php
namespace CG\RoyalMailApi\Response\Manifest;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\DocumentInterface;
use CG\CourierAdapter\ManifestInterface;

class Response implements ManifestInterface
{
    /** @var string */
    protected $manifest;
    /** @var string */
    protected $reference;
    /** @var Account */
    protected $account;

    public function __construct(Account $account, ?string $manifest = null, ?string $reference = null)
    {
        $this->manifest = $manifest;
        $this->reference = $reference;
        $this->account = $account;
    }

    public function getType()
    {
        return DocumentInterface::TYPE_PDF;
    }

    public function getData()
    {
        return $this->manifest;
    }

    public function getAccount()
    {
        $this->account;
    }

    public function getReference()
    {
        return $this->reference;
    }
}
