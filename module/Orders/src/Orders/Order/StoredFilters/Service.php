<?php
namespace Orders\Order\StoredFilters;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\View\Model\ViewModel;
use CG\UserPreference\Shared\Entity as UserPreference;

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

    public function addStoredFilter(UserPreference $userPreference, $filterName, array $filterData)
    {
        $preference = $userPreference->getPreference();

        if (!isset($preference['order-saved-filters'])) {
            $preference['order-saved-filters'] = [];
        }

        $preference['order-saved-filters'][$filterName] = $filterData;

        $userPreference->setPreference($preference);
    }

    public function removeStoredFilter(UserPreference $userPreference, $filterName)
    {
        $preference = $userPreference->getPreference();

        if (!isset($preference['order-saved-filters'], $preference['order-saved-filters'][$filterName])) {
            return;
        }

        unset($preference['order-saved-filters'][$filterName]);

        $userPreference->setPreference($preference);
    }

    public function getStoredFilters(UserPreference $userPreference, $jsonEncodeFilter = false)
    {
        $preference = $userPreference->getPreference();
        $storedFilters = [];

        if (!isset($preference['order-saved-filters'])) {
            return $storedFilters;
        }

        foreach ($preference['order-saved-filters'] as $filterName => $filterData) {
            if ($jsonEncodeFilter) {
                $filterData = json_encode($filterData);
            }

            $storedFilters[] = [
                'name' => $filterName,
                'filter' => $filterData,
            ];
        }

        return $storedFilters;
    }

    /**
     * @param UserPreference $userPreference
     * @return ViewModel
     */
    public function getStoredFiltersSidebarView(UserPreference $userPreference)
    {
        $storedFilters = array_reverse($this->getStoredFilters($userPreference, true));
        $storedFiltersSidebar = $this->newViewModel(['filters' => $storedFilters]);
        $storedFiltersSidebar->setTemplate('orders/orders/sidebar/storedFilters');
        return $storedFiltersSidebar;
    }
}