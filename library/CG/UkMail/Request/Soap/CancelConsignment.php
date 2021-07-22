<?php
namespace CG\UkMail\Request\Soap;

use CG\UkMail\Request\AbstractPostRequest;
use CG\UkMail\Response\Soap\CancelConsignment as Response;

class CancelConsignment extends AbstractPostRequest implements RequestInterface
{
    protected const URI = 'Services/UKMConsignmentServices/UKMConsignmentService.svc?wsdl';

    protected const ENVELOPE = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://www.UKMail.com/Services/Contracts/ServiceContracts" xmlns:dat="http://www.UKMail.com/Services/Contracts/DataContracts"></soapenv:Envelope>';

    /** @var string */
    protected $username;
    /** @var string */
    protected $authenticationToken;
    /** @var string */
    protected $consignmentNumber;

    public function __construct(string $username, string $authenticationToken, string $consignmentNumber)
    {
        $this->username = $username;
        $this->authenticationToken = $authenticationToken;
        $this->consignmentNumber = $consignmentNumber;
    }

    protected function getBody(): string
    {
        $xml = new \SimpleXMLElement(static::ENVELOPE);
        $xml->addChild('soapenv:Header');
        $body = $xml->addChild('soapenv:Body');
        $cancelConsignment = $body->addChild('ser:CancelConsignment');
        $request = $cancelConsignment->addChild('ser:request');
        $request->addChild('dat:AuthenticationToken', $this->getAuthenticationToken());
        $request->addChild('dat:Username', $this->getUsername());
        $request->addChild('dat:ConsignmentNumber', $this->getConsignmentNumber());

        return $this->removeXmlDeclaration($xml->asXML());
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'text/xml',
            'Expect' => ''
        ];
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);
        unset($options['json']);
        return [
            'headers' => array_merge($options['headers'] ?? [], $this->getHeaders()),
            'body' => $this->getBody()
        ];
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): CancelConsignment
    {
        $this->username = $username;
        return $this;
    }

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(string $authenticationToken): CancelConsignment
    {
        $this->authenticationToken = $authenticationToken;
        return $this;
    }

    public function getConsignmentNumber(): string
    {
        return $this->consignmentNumber;
    }

    public function setConsignmentNumber(string $consignmentNumber): CancelConsignment
    {
        $this->consignmentNumber = $consignmentNumber;
        return $this;
    }

    protected function removeXmlDeclaration(string $resultXml): string
    {
        return preg_replace("/<\\?xml.*\\?>/",'', $resultXml,1);
    }
}