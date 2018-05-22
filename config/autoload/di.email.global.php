<?php
use CG\Order\Client\Invoice\Email\Service as EmailInvoiceService;
use Zend\I18n\Translator\TranslatorServiceFactory;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'InvoiceEmailView' => ViewModel::class,
            ],
            EmailInvoiceService::class => [
                'parameters' => [
                    'config' => 'app_config',
                    'mailer' => 'orderhub-mailer',
                    'emailView' => 'InvoiceEmailView'
                ],
            ],
            'InvoiceEmailView' => [
                'parameters' => [
                    'template' => 'orderhub/email_invoice',
                ],
            ],
            TemplatePathStack::class => [
                'parameters' => [
                    'options' => [
                        'script_paths' => [
                            PROJECT_ROOT . '/vendor/channelgrabber/zf2-email-template/view',
                        ],
                    ],
                ],
            ],
            PhpRenderer::class => [
                'parameters' => [
                    'resolver' => TemplatePathStack::class
                ],
            ],
        ],
    ],
];
