<?php
namespace Orders\Order\Invoice\Renderer\Service;

use Orders\Order\Invoice\Renderer\ServiceInterface;
use Zend\Di\Di;
use CG\Template\ReplaceManager\OrderContent as TagReplacer;
use CG\Template\Renderer\Pdf as Renderer;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity as Template;
use CG\Template\Renderer\Pdf\Document;
use CG\Template\Element\Page;
use ZendPdf\PdfDocument;

class Pdf implements ServiceInterface
{
    protected $di;
    protected $tagReplacer;
    protected $renderer;
    protected $pdf;

    public function __construct(Di $di, TagReplacer $tagReplacer, Renderer $renderer)
    {
        $this->setDi($di)->setTagReplacer($tagReplacer)->setRenderer($renderer);
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

    public function setTagReplacer(TagReplacer $tagReplacer)
    {
        $this->tagReplacer = $tagReplacer;
        return $this;
    }

    /**
     * @return TagReplacer
     */
    public function getTagReplacer()
    {
        return $this->tagReplacer;
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

    public function getMimeType()
    {
        return 'application/pdf';
    }

    public function getFileName($filename = 'Invoice')
    {
        return $filename . '.pdf';
    }

    public function renderOrderTemplate(Order $order, Template $orderTemplate)
    {
        $document = $this->getDi()->newInstance(
            Document::class,
            [
                'document' => $this->getDi()->newInstance(PdfDocument::class),
                'height' => $orderTemplate->getPaperPage()->getHeight(),
                'width' => $orderTemplate->getPaperPage()->getWidth()
            ]
        );

        $this->getTagReplacer()->render($orderTemplate, $order);
        return $this->getRenderer()->renderPages($orderTemplate, $document);
    }

    public function initializeNewDocument()
    {
        $this->pdf = new PdfDocument();
    }

    public function addPage($page)
    {
        $this->pdf->pages[] = clone $page;
    }

    public function renderDocument()
    {
        return $this->pdf->render();
    }

    public function combine(array $renderedContent)
    {
        $pdf = new PdfDocument();
        foreach ($renderedContent as $pdfContent) {
            $currentPdf = PdfDocument::parse($pdfContent);
            foreach ($currentPdf->pages as $page) {
                $pdf->pages[] = clone $page;
            }
        }
        return $pdf->render();
    }

    public function combinePages()
    {
        return $this->pdf->render();
    }
}