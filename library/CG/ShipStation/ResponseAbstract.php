<?php
namespace CG\ShipStation;

abstract class ResponseAbstract implements ResponseInterface
{
    /** @var string */
    protected $jsonResponse;

    abstract protected static function build($decodedJson);

    public static function createFromJson(string $json)
    {
        $decodedJson = json_decode($json);
        if ($decodedJson === null) {
            throw new \RuntimeException('JSON response from ShipStation could not be decoded: '.json_last_error_msg());
        }
        /** @var ResponseAbstract $response */
        $response = static::build($decodedJson);
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
