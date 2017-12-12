<?php
namespace CG\ShipStation;

abstract class ResponseAbstract implements ResponseInterface
{
    /** @var string */
    protected $jsonResponse;

    abstract protected function build($decodedJson);

    public function createFromJson(string $json)
    {
        $this->jsonResponse = $json;
        return $this->build(json_decode($json));
    }

    public function getJsonResponse(): ?string
    {
        return $this->jsonResponse;
    }
}
