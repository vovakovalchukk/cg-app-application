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

    public function renderOrderTemplate(Order $order, Template $template)
    {
        $document = $this->getDi()->newInstance(
            Document::class,
            [
                'document' => $this->getDi()->newInstance(PdfDocument::class),
                'page' => $this->getDi()->newInstance(Page::class)
            ]
        );
        $template->expandPage($document->getPaperPage());
        $this->getTagReplacer()->render($template, $order);
        return $this->getRenderer()->render($template, $document);
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
}