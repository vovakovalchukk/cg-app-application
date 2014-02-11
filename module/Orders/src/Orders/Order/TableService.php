<?php
namespace Orders\Order;

use Zend\Di\Di;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\DataTable;
use CG\Order\Shared\Tag\StorageInterface as TagStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Shared\Tag\Entity as Tag;
use Zend\View\Model\ViewModel;

class TableService
{
    protected $di;
    protected $activeUserContainer;
    protected $tagClient;
    protected $ordersTable;

    public function __construct(Di $di, ActiveUserInterface $activeUserContainer, TagStorage $tagClient, DataTable $ordersTable)
    {
        $this
            ->setDi($di)
            ->setActiveUserContainer($activeUserContainer)
            ->setTagClient($tagClient)
            ->setOrdersTable($ordersTable);
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

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    /**
     * @return User
     */
    public function getActiveUser()
    {
        return $this->getActiveUserContainer()->getActiveUser();
    }

    public function setTagClient(TagStorage $tagClient)
    {
        $this->tagClient = $tagClient;
        return $this;
    }

    /**
     * @return TagStorage
     */
    public function getTagClient()
    {
        return $this->tagClient;
    }

    public function setOrdersTable(DataTable $ordersTable)
    {
        if ($this->ordersTable === $ordersTable) {
            return $this;
        }
        $this->ordersTable = $ordersTable;
        $this->configureOrdersTable();
        return $this;
    }

    protected function configureOrdersTable()
    {
        $this->addTagColumns();
    }

    protected function addTagColumns()
    {
        try {
            $tags = $this->getTagClient()->fetchCollectionAll(
                1,
                'all',
                $this->getActiveUser()->getAvailableOrganisationUnitIds(),
                []
            );

            foreach ($tags as $tag) {
                $this->addTagColumn($tag);
            }
        } catch (NotFound $exception) {
            // No Tags -- Nothing to do
        }
    }

    protected function addTagColumn(Tag $tag)
    {
        $ordersTable = $this->getOrdersTable();

        $viewModel = $this->getDi()->newInstance(
            ViewModel::class,
            [
                'variables' => [
                    'id' => $ordersTable->getVariable('id'),
                    'tag' => htmlentities($tag->getTag(), ENT_QUOTES)
                ],
                'template' => 'orders/orders/table/header/tag'
            ]
        );

        $column = $this->getDi()->newInstance(
            DataTable\Column::class,
            [
                'column' => $tag->getTag(),
                'templateId' => 'custom-tag',
                'viewModel' => $viewModel,
                'defaultContent' => ''
            ]
        );

        $ordersTable->addColumn($column);
    }

    /**
     * @return DataTable
     */
    public function getOrdersTable()
    {
        return $this->ordersTable;
    }
}