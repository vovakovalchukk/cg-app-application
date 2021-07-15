<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\Request\AbstractRequest;
use CG\UkMail\Response\Rest\Authenticate as Response;

class Authenticate extends AbstractRequest implements RequestInterface
{
    protected const METHOD = 'GET';
    protected const URI = 'gateway/SSOAuthenticationAPI/1.0/ssoAuth/users/authenticate';

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function getUri(): string
    {
        return static::URI;
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);

        $options['headers'] = $this->getHeaders();
        $options['query'] = $this->getQuery();

        return $options;

    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function getHeaders(): array
    {
        return [];
    }

    protected function getQuery(): array
    {
        return [

        ];
    }
}