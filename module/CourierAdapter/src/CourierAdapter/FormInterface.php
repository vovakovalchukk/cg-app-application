<?php
namespace CourierAdapter;

use Zend\View\Model\ViewModel;

interface FormInterface
{
    public function getFormView(string $channelName, string $goBackUrl, string $saveUrl, ?int $accountId = null, ?string $requestUri = null): ViewModel;
}