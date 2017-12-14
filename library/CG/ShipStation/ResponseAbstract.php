<?php
namespace CG\ShipStation;

abstract class ResponseAbstract implements ResponseInterface
{
    /** @var string */
    protected $jsonResponse;

    abstract protected static function build($decodedJson);

    public static function createFromJson(string $json)
    {
        /** @var ResponseAbstract $response */
        $response = static::build($json);
        $response->setJsonResponse($json);
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
