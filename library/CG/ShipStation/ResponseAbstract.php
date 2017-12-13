<?php
namespace CG\ShipStation;

abstract class ResponseAbstract implements ResponseInterface
{
    /** @var string */
    protected $jsonResponse;

    abstract protected function build($decodedJson);

    public static function createFromJson(string $json)
    {
        $response = new static();
        $response->setJsonResponse($json);
        $response->build(json_decode($json));
        return $response;
    }

    public function getJsonResponse(): ?string
    {
        return $this->jsonResponse;
    }

    public function setJsonResponse(string $jsonResponse)
    {
        $this->jsonResponse = $jsonResponse;
        return $this;
    }
}
