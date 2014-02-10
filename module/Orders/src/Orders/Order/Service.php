<?php
namespace Orders\Order;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\DataTable;
use CG_UI\View\Table;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Rows as TableRows;
use CG\Order\Shared\StorageInterface;
use CG\User\ActiveUserInterface;
use CG\Order\Service\Filter;
use CG\Order\Shared\Entity;
use CG\Order\Shared\Item\Entity as ItemEntity;
use Zend\Di\Di;
use Zend\I18n\View\Helper\CurrencyFormat;
use CG\User\Service as UserService;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Note\Collection as OrderNoteCollection;
use CG\Order\Shared\UserChange\Entity as UserChangeEntity;

class Service
{
    protected $ordersTable;
    protected $orderClient;
    protected $userService;
    protected $activeUserContainer;
    protected $di;

    public function __construct(
        DataTable $ordersTable,
        StorageInterface $orderClient,
        UserService $userService,
        ActiveUserInterface $activeUserContainer,
        Di $di
    )
    {
        $this
            ->setOrdersTable($ordersTable)
            ->setOrderClient($orderClient)
            ->setUserService($userService)
            ->setActiveUserContainer($activeUserContainer)
            ->setDi($di);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function setOrdersTable(DataTable $ordersTable)
    {
        $this->ordersTable = $ordersTable;
        return $this;
    }

    public function getOrdersTable()
    {
        return $this->ordersTable;
    }

    public function setOrderClient(StorageInterface $orderClient)
    {
        $this->orderClient = $orderClient;
        return $this;
    }

    public function getOrderClient()
    {
        return $this->orderClient;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
        return $this;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function getOrders(Filter $filter)
    {
        return $this->getOrderClient()->fetchCollectionByFilter($filter);
    }

    public function getOrder($orderId)
    {
        return $this->getOrderClient()->fetch($orderId);
    }

    public function getOrderItemTable(Entity $order)
    {
        $getDiscountTotal = function (ItemEntity $entity) {
            return $entity->getIndividualItemDiscountPrice() * $entity->getItemQuantity();
        };

        $getTaxTotal = function (ItemEntity $entity) {
            return $entity->getItemTaxPercentage() * $entity->getItemQuantity();
        };

        $getLineTotal = function (ItemEntity $entity) {
            return $entity->getIndividualItemPrice() * $entity->getItemQuantity();
        };

        $numberFormat = $this->getDi()->get(CurrencyFormat::class);
        $currencyCode = $order->getCurrencyCode();
        $currencyFormatter = function (ItemEntity $entity, $value) use ($numberFormat, $currencyCode) {
            return $numberFormat($value, $currencyCode);
        };

        $columns = [
            ['name' => 'SKU', 'class' => '', 'getter' => 'getItemSku', 'callback' => null],
            ['name' => 'Product Name', 'class' => '', 'getter' => 'getItemName', 'callback' => null],
            ['name' => 'Quantity', 'class' => 'right', 'getter' => 'getItemQuantity', 'callback' => null],
            ['name' => 'Individual Price', 'class' => 'right', 'getter' => 'getIndividualItemPrice', 'callback' => $currencyFormatter],
            ['name' => 'Individual Discount', 'class' => 'right', 'getter' => 'getIndividualItemDiscountPrice', 'callback' => $currencyFormatter],
            ['name' => 'Tax', 'class' => 'right', 'getter' => 'getItemTaxPercentage', 'callback' => null],
            ['name' => 'Discount Total', 'class' => 'right', 'getter' => $getDiscountTotal, 'callback' => $currencyFormatter],
            ['name' => 'Tax Total', 'class' => 'right', 'getter' => $getTaxTotal, 'callback' => $currencyFormatter],
            ['name' => 'Line Total', 'class' => 'right', 'getter' => $getLineTotal, 'callback' => $currencyFormatter],
        ];

        $table = $this->getDi()->newInstance(Table::class);
        $mapping = [];
        foreach ($columns as $column) {
            $table->addColumn($this->getDi()->newInstance(TableColumn::class, ["name" => $column["name"], "class" => $column["class"]]));
            $mapping[$column["name"]] = ["getter" => $column["getter"], "callback" => $column["callback"]];
        }
        $rows = $this->getDi()->newInstance(TableRows::class, ["data" => $order->getItems(), "mapping" => $mapping]);
        $table->setRows($rows);
        $table->setTemplate('table/standard');
        return $table;
    }

    public function getNamesFromOrderNotes(OrderNoteCollection $notes)
    {
        $itemNotes = array();
        foreach ($notes as $note) {
            $itemNote = $note->toArray();
            $itemNote["eTag"] = $note->getETag();
            $itemNotes[] = $itemNote;
        }
        $userIds = array();
        foreach ($itemNotes as $itemNote) {
            $userIds[] = $itemNote["userId"];
        }
        if (empty($userIds)) {
            return $itemNotes;
        }
        $userIds = array_unique($userIds);
        try {
            $users = $this->getUserService()->fetchCollection("all", null, null, null, $userIds);
            foreach ($itemNotes as &$note) {
                $user = $users->getById($note["userId"]);
                $note["author"] = $user->getFirstName() . " " . $user->getLastName();
            }
        } catch (NotFound $e) {
            //no users found for notes, don't return any authors
        }

        return $itemNotes;
    }

    public function saveOrder(Order $entity)
    {
        return $this->getOrderClient()->save($entity);
    }

    public function archiveOrder(Order $entity)
    {
        return $this->getOrderClient()->archive($entity);
    }
}