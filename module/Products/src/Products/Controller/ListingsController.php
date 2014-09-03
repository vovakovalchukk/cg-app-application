<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Products\Listing\Service as ListingService;
use Products\Listing\BulkActions\Service as BulkActionsService;

class ListingsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX = 'listingsImport';
    const ROUTE_INDEX_URL = '/listing/import';

    protected $viewModelFactory;
    protected $listingService;
    protected $bulkActionsService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ListingService $listingService,
        BulkActionsService $bulkActionsService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setListingService($listingService)
            ->setBulkActionsService($bulkActionsService);
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $bulkActions = $this->getBulkActionsService()->getListPageBulkActions();
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('products/listings/bulk-actions/index');
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');
        $bulkAction->setVariable('isHeaderBarVisible', $this->getListingService()->isFilterBarVisible());
        $view->setVariable('isHeaderBarVisible', $this->getListingService()->isFilterBarVisible());
        return $view;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    protected function getBulkActionsService()
    {
        return $this->bulkActionsService;
    }

    protected function setListingService(ListingService $listingService)
    {
        $this->listingService = $listingService;
        return $this;
    }

    protected function getListingService()
    {
        return $this->listingService;
    }
}
