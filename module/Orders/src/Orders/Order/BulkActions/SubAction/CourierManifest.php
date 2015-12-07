<?php
namespace Orders\Order\BulkActions\SubAction;

use CG_UI\View\BulkActions\SubAction;
use Orders\Controller\CourierController;
use Zend\View\Model\ViewModel;

class CourierManifest extends SubAction
{
    public function __construct(ViewModel $urlView, array $elementData = [], ViewModel $javascript = null)
    {
        parent::__construct('Manifests', 'courierManifest', $elementData, $javascript);
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
                'route' => 'Orders/' . CourierController::ROUTE,
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