<?php
namespace CG\CourierAdapter\Provider;

use CG\Account\CredentialsInterface;

class Credentials implements CredentialsInterface
{
    protected $requiredFields = [];
    protected $data = [];

    public function __construct(array $requiredFields = [])
    {
        $this->requiredFields = $requiredFields;
    }

    public function isEmpty()
    {
        foreach ($this->requiredFields as $requiredField) {
            if (!isset($this->data[$requiredField])) {
                return true;
            }
        }
        return false;
    }

    public function toArray()
    {
        return $this->getData();
    }

    public function getData()
    {
        return $this->data;
    }

    public function get($field)
    {
        if (isset($this->data[$field])) {
            return $this->data[$field];
        }
        throw new \InvalidArgumentException('Unknown credentials field "'.$field.'"');
    }

    public function set($field, $value)
    {
        $this->data[$field] = $value;
        return $this;
    }
}
