<?php
namespace CG\Intersoft;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\DocumentInterface;
use CG\CourierAdapter\ManifestInterface;

class Manifest implements ManifestInterface
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
        return $this->account;
    }

    public function getReference()
    {
        return $this->reference;
    }
}
