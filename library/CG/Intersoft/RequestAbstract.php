<?php
namespace CG\Intersoft;

use CG\Intersoft\Credentials;
use SimpleXMLElement;
use CG\Intersoft\RequestInterface;;

abstract class RequestAbstract implements RequestInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var Credentials */
    protected $credentials;
    /** @var string */

    public function setCredentials(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    protected function getApplicationId(): string
    {
        return $this->credentials->getApplicationId();
    }

    protected function getUserId(): string
    {
        return $this->credentials->getUserId();
    }

    protected function getPassword(): string
    {
        return $this->credentials->getPassword();
    }

    protected function generateTransactionId(): string
    {
        return md5(uniqid());
    }

    protected function addIntegrationHeader(SimpleXMLElement $xml): SimpleXMLElement
    {
        $integrationHeader = $xml->addChild('integrationHeader');
        $integrationHeader->addChild('dateTimeStamp', date(static::DATE_FORMAT));
        $integrationHeader->addChild('transactionId', $this->generateTransactionId());
        $integrationHeader->addChild('applicationId', $this->getApplicationId());
        $integrationHeader->addChild('userId', $this->getUserId());
        $integrationHeader->addChild('password', $this->getPassword());
        return $xml;
    }
}