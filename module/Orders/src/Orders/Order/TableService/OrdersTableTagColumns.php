<?php
namespace Orders\Order\TableService;

use Zend\Di\Di;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\DataTable;
use CG\Order\Shared\Tag\StorageInterface as TagStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Shared\Tag\Entity as Tag;
use Zend\View\Model\ViewModel;

class OrdersTableTagColumns implements OrdersTableModifierInterface
{
    protected $di;
    protected $activeUserContainer;
    protected $tagClient;

    public function __construct(Di $di, ActiveUserInterface $activeUserContainer, TagStorage $tagClient)
    {
        $this->setDi($di)->setActiveUserContainer($activeUserContainer)->setTagClient($tagClient);
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

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

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

    public function getTagClient()
    {
        return $this->tagClient;
    }

    public function modifyTable(DataTable $ordersTable)
    {
        try {
            $tags = $this->getTagClient()->fetchCollectionAll(
                1,
                'all',
                $this->getActiveUser()->getAvailableOrganisationUnitIds(),
                []
            );

            foreach ($tags as $tag) {
                $this->addTagColumn($ordersTable, $tag);
            }
        } catch (NotFound $exception) {
            // No Tags -- Nothing to do
        }
    }

    protected function addTagColumn(DataTable $ordersTable, Tag $tag)
    {
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
}