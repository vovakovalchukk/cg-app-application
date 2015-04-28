<?php
namespace Orders\Order\Csv;

use Zend\Http\Response as HttpResponse;

class Response extends HttpResponse
{
    public function __construct($mimeType, $filename, $content)
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
