<?php
namespace Orders\Order\Invoice;

use Zend\Http\Response as HttpResponse;

class Response extends HttpResponse
{
    public function __construct($content, $filename = 'invoice.pdf', $mimeType = 'application/pdf')
    {
        $this->setMimeType($mimeType)->setFilename($filename)->setContent($content);
    }

    protected function setMimeType($mimeType)
    {
        $this->getHeaders()->addHeaderLine('Content-Type', $mimeType);
        return $this;
    }

    protected function setFilename($filename)
    {
        $this->getHeaders()->addHeaderLine('Content-Disposition', 'attachment; filename="' . $filename . '"');
        return $this;
    }
}