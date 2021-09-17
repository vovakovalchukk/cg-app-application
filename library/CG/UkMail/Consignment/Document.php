<?php
namespace CG\UkMail\Consignment;

class Document
{
    /** @var string|null */
    protected $document;

    public function __construct(?string $document)
    {
        $this->document = $document;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }
}