<?php
namespace Orders\Order\Invoice\Renderer\Service;

use Orders\Order\Invoice\Renderer\ServiceInterface;
use Zend\Di\Di;
use CG\Template\Renderer\Pdf as Renderer;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity as Template;
use CG\Template\Renderer\Pdf\Document;
use ZendPdf\PdfDocument;

class Pdf implements ServiceInterface
{
    protected $di;
    protected $renderer;

    public function __construct(Di $di, Renderer $renderer)
    {
        $this->setDi($di)->setRenderer($renderer);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    public function renderOrderTemplate(Order $order, Template $template)
    {
        $document = $this->getDi()->get(Document::class);
        $template->expandPage($document->getPaperPage());
        return $this->getRenderer()->render($template, $document);
    }

    public function combine(array $renderedContent)
    {
        $pdf = new PdfDocument();
        foreach ($renderedContent as $pdfContent) {
            $currentPdf = PdfDocument::parse($pdfContent);
            foreach ($currentPdf->pages as $page) {
                $pdf->pages[] = $page;
            }
        }
        return $pdf;
    }
}