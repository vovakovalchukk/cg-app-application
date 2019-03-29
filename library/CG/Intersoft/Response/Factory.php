<?php
namespace CG\Intersoft\Response;

use CG\Intersoft\RequestInterface;
use CG\Intersoft\ResponseInterface;
use CG\Intersoft\Response\MapperInterface;
use CG\Intersoft\Response\FromXmlInterface;
use SimpleXMLElement;
use stdClass;
use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(RequestInterface $request, SimpleXMLElement $xml): ResponseInterface
    {
        $responseClass = $request->getResponseClass();
        $mapperClass = $responseClass.'\\Mapper';
        if (!class_exists($mapperClass)) {
            return $this->createFromStatic($responseClass, $xml);
        }
        return $this->createFromMapper($mapperClass, $xml);
    }

    protected function createFromStatic(string $responseClass, SimpleXMLElement $xml): ResponseInterface
    {
        if (!in_array(FromXmlInterface::class, class_implements($responseClass))) {
            throw new \RuntimeException($responseClass .' does not implement ' . FromXmlInterface::class);
        }
        return $responseClass::fromXml($xml);
    }

    protected function createFromMapper(string $mapperClass, SimpleXMLElement $xml): ResponseInterface
    {
        $mapper = $this->di->get($mapperClass);
        if (!$mapper instanceof MapperInterface) {
            throw new \RuntimeException($mapperClass .' does not implement ' . MapperInterface::class);
        }
        return $mapper->fromXml($xml);
    }
}