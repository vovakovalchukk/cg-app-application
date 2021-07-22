<?php
namespace CG\UkMail\Consignment;

class Document
{
    /** @var string */
    protected $document;

    public function __construct(string $document)
    {
        $this->document = $document;
    }

    public function getDocument(): string
    {
        return $this->document;
    }
}