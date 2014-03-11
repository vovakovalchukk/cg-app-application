<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class InvoiceController extends AbstractActionController
{
    public function generateAction()
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();

        $headers->addHeaderLine('Content-Type', 'application/pdf');
        $headers->addHeaderLine('Content-Disposition', 'attachment; filename="invoice.pdf"');

        return $response->setContent('');
    }
}