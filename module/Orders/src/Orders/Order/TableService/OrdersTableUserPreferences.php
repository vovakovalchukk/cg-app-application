<?php
namespace Orders\Order\TableService;

use CG_UI\View\DataTable;
use CG_UI\View\Table\Column as TableColumn;
use CG\User\ActiveUserInterface;
use CG\UserPreference\Client\Service as UserPreferenceService;
use CG\UserPreference\Shared\Entity as UserPreference;
use CG_UI\View\DataTable\Column;

class OrdersTableUserPreferences implements OrdersTableModifierInterface
{
    const COL_PREF_KEY = 'order-columns';
    const COL_POS_PREF_KEY = 'order-column-positions';
    const SIDEBAR_STATE_KEY = 'order-sidebar-state';
    const FILTER_BAR_STATE_KEY = 'order-filter-bar-state';

    /** @var ActiveUserInterface $activeUser */
    protected $activeUser;
    /** @var UserPreferenceService $userPreferenceService */
    protected $userPreferenceService;
    /** @var UserPreference $userPreference */
    protected $userPreference;

    public function __construct(ActiveUserInterface $activeUser, UserPreferenceService $userPreferenceService)
    {
        $this->activeUser = $activeUser;
        $this->userPreferenceService = $userPreferenceService;
    }

    /**
     * @return UserPreference
     */
    public function getUserPreference()
    {
        if (!isset($this->userPreference)) {
            $activeUserId = $this->activeUser->getActiveUser()->getId();
            $this->userPreference = $this->userPreferenceService->fetch($activeUserId);
        }
        return $this->userPreference;
    }

    /**
     * @return array
     */
    public function fetchUserPrefOrderColumns()
    {
        return $this->fetchUserPrefItem(static::COL_PREF_KEY);
    }

    /**
     * @return self
     */
    public function updateUserPrefOrderColumns(array $updatedColumns)
    {
        $storedColumns = $this->fetchUserPrefOrderColumns();
        foreach ($updatedColumns as $name => $on) {
            $storedColumns[$name] = $on;
        }
        $this->saveUserPrefItem(static::COL_PREF_KEY, $storedColumns);
        return $this;
    }

    /**
     * @return array
     */
    public function fetchUserPrefOrderColumnPositions()
    {
        return $this->fetchUserPrefItem(static::COL_POS_PREF_KEY);
    }

    /**
     * @return self
     */
    public function updateUserPrefOrderColumnPositions(array $columnPositions)
    {
        $this->saveUserPrefItem(static::COL_POS_PREF_KEY, $columnPositions);
        return $this;
    }

    /**
     * @return array
     */
    public function fetchUserPrefItem($key)
    {
        $userPrefsPref = $this->getUserPreference()->getPreference();
        return (isset($userPrefsPref[$key]) ? $userPrefsPref[$key] : []);
    }

    /**
     * @return self
     */
    public function saveUserPrefItem($key, $value)
    {
        $userPrefs = $this->getUserPreference();
        $userPrefsPref = $userPrefs->getPreference();
        $userPrefsPref[$key] = $value;
        $userPrefs->setPreference($userPrefsPref);
        $this->userPreferenceService->save($userPrefs);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSidebarVisible()
    {
        $preference = $this->getUserPreference()->getPreference();
        $visible = isset($preference[static::SIDEBAR_STATE_KEY]) ? $preference[static::SIDEBAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     */
    public function isFilterBarVisible()
    {
        $preference = $this->getUserPreference()->getPreference();
        $visible = isset($preference[static::FILTER_BAR_STATE_KEY]) ? $preference[static::FILTER_BAR_STATE_KEY] : true;
        return filter_var($visible, FILTER_VALIDATE_BOOLEAN);
    }

    public function modifyTable(DataTable $ordersTable)
    {
        $associativeColumns = [];
        /** @var Column $column */
        foreach ($ordersTable->getColumns() as $column) {
            $associativeColumns[$column->getColumn()] = $column;
        }

        $columnPrefs = $this->fetchUserPrefOrderColumns();
        foreach ($columnPrefs as $name => $on) {
            if (!isset($associativeColumns[$name])) {
                continue;
            }
            $associativeColumns[$name]->setVisible(
                filter_var($on, FILTER_VALIDATE_BOOLEAN)
            );
        }

        $columnPosPrefs = $this->fetchUserPrefOrderColumnPositions();
        foreach ($columnPosPrefs as $name => $pos) {
            if (!isset($associativeColumns[$name])) {
                continue;
            }
            $associativeColumns[$name]->setOrder($pos);
        }

        $ordersTable->reorderColumns();
    }
}
