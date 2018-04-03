<?php
namespace CG\CourierExport;

use CG\Account\CredentialsInterface;

class Credentials implements CredentialsInterface
{
    /** @var array */
    protected $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function isEmpty()
    {
        return false;
    }

    public function toArray()
    {
        return $this->params;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function getParam(string $param, $default = null)
    {
        return $this->params[$param] ?? $default;
    }
}