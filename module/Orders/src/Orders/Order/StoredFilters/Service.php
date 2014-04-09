<?php
namespace Orders\Order\StoredFilters;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;
use CG\User\Entity as User;

class Service
{
    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->setViewModelFactory($viewModelFactory);
    }

    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    /**
     * @param $variables
     * @param $options
     * @return ViewModel
     */
    protected function newViewModel($variables = null, $options = null)
    {
        return $this->getViewModelFactory()->newInstance($variables, $options);
    }

    public function getStoredFilters(User $user)
    {
        return [];
    }

    /**
     * @param User $user
     * @return ViewModel
     */
    public function getStoredFiltersSidebarView(User $user)
    {
        $storedFiltersSidebar = $this->newViewModel(['filters' => $this->getStoredFilters($user)]);
        $storedFiltersSidebar->setTemplate('orders/orders/sidebar/filters');
        return $storedFiltersSidebar;
    }
}