<?php
namespace CG\ShipStation\Response\Shipping\Manifest;

use CG\ShipStation\Messages\Downloadable;
use CG\ShipStation\ResponseAbstract;

class Create extends ResponseAbstract
{

    /** @var string */
    protected $formId;
    /** @var Downloadable */
    protected $manifestDownload;

    public function __construct(string $formId, Downloadable $manifestDownload)
    {
        $this->formId = $formId;
        $this->manifestDownload = $manifestDownload;
    }

    protected static function build($decodedJson)
    {
        $errors = [];
        if (isset($decodedJson->errors)) {
            foreach ($decodedJson->errors as $errorJson) {
                $errors[] = $errorJson->message;
            }
        }

        return new static(
            $decodedJson->form_id,
            isset($decodedJson->manifest_download) ? Downloadable::build($decodedJson->manifest_download) : null
        );
    }

    public function getFormId(): string
    {
        return $this->formId;
    }

    public function getManifestDownload(): Downloadable
    {
        return $this->manifestDownload;
    }
}