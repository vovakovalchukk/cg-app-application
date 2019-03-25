<?php
namespace CG\RoyalMailApi\Response;

use CG\RoyalMailApi\RequestInterface;
use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\MapperInterface;
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

    public function __invoke(RequestInterface $request, stdClass $json): ResponseInterface
    {
        $responseClass = $request->getResponseClass();
        $mapperClass = $responseClass.'\\Mapper';
        if (!class_exists($mapperClass)) {
            return $this->createFromStatic($responseClass, $json);
        }
        return $this->createFromMapper($mapperClass, $json);
    }

    protected function createFromStatic(string $responseClass, stdClass $json): ResponseInterface
    {
        if (!in_array(FromJsonInterface::class, class_implements($responseClass))) {
            throw new \RuntimeException($responseClass .' does not implement ' . FromJsonInterface::class);
        }
        return $responseClass::fromJson($json);
    }

    protected function createFromMapper(string $mapperClass, stdClass $json): ResponseInterface
    {
        $mapper = $this->di->get($mapperClass);
        if (!$mapper instanceof MapperInterface) {
            throw new \RuntimeException($mapperClass .' does not implement ' . MapperInterface::class);
        }
        return $mapper->fromJson($json);
    }
}