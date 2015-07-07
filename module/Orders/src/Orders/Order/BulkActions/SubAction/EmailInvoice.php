<?php
namespace Orders\Order\BulkActions\SubAction;

use CG_UI\View\BulkActions\SubAction;
use Zend\View\Model\ViewModel;

class EmailInvoice extends SubAction
{
    /**
     * @var ViewModel $urlView
     */
    protected $urlView;

    public function __construct(ViewModel $urlView, array $elementData = [], ViewModel $javascript = null)
    {
        parent::__construct('Send by Email', 'emailInvoice', $elementData, $javascript);
        $this->setUrlView($urlView)->configure();
    }

    protected function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        $this->urlView->setVariables(
            [
                'route' => 'Orders/invoice/invoice_email',
                'parameters' => []
            ]
        );
        return $this;
    }

    protected function configure()
    {
        $this->addElementView($this->urlView);
        return $this;
    }
} 