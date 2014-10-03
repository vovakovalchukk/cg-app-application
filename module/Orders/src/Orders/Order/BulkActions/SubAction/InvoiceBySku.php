<?php
namespace Orders\Order\BulkActions\SubAction;

use CG_UI\View\BulkActions\SubAction;
use Zend\View\Model\ViewModel;

class InvoiceBySku extends SubAction
{
    public function __construct(ViewModel $urlView, array $elementData = [], ViewModel $javascript = null)
    {
        parent::__construct('Invoice', 'by SKU', $elementData, $javascript);
        $this->setUrlView($urlView)
            ->configure();
    }

    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        return $this;
    }

    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Orders/invoice/invoice_bysku',
                'parameters' => []
            ]
        );
        return $this->urlView;
    }

    protected function configure()
    {
        $this->addElementView($this->getUrlView());
        return $this;
    }
}