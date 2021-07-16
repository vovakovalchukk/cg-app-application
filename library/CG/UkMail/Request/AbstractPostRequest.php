<?php
namespace CG\UkMail\Request;

abstract class AbstractPostRequest extends AbstractRequest
{
    protected const METHOD = 'POST';

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);
        $options['json'] = $this->getBody();
        return $options;
    }

    abstract protected function getBody(): array;

    protected function prepareRequestData(array $requestData): array
    {
        $requestData = array_filter($requestData, [$this, 'filterRequestValue']);
        array_walk(
            $requestData,
            function(&$requestData) {
                if (is_array($requestData)) {
                    $requestData = $this->prepareRequestData($requestData);
                }
            }
        );
        return $requestData;
    }

    protected function filterRequestValue($requestValue)
    {
        return is_array($requestValue) ? !empty($requestValue) : !is_null($requestValue);
    }
}