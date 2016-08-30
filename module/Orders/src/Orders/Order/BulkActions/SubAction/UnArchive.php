<?php
namespace Orders\Order\BulkActions\SubAction;

use CG_UI\View\BulkActions\SubAction;
use Orders\Module;
use Zend\View\Model\ViewModel;

class UnArchive extends SubAction
{
    /** @var ViewModel $urlView */
    protected $urlView;

    public function __construct(ViewModel $urlView, array $elementData = [], ViewModel $javascript = null)
    {
        parent::__construct('Un-Archive', 'unArchive', $elementData, $javascript);
        $this->setUrlView($urlView)->configure();
    }

    /**
     * @return self
     */
    protected function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        $this->urlView->setVariables(
            [
                'route' => implode('/', [Module::ROUTE, 'archive', 'unarchive']),
                'parameters' => []
            ]
        );
        return $this;
    }

    /**
     * @return self
     */
    protected function configure()
    {
        $this->addElementView($this->urlView);
        return $this;
    }
}
