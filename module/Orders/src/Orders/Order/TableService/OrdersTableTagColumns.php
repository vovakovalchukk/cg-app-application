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
use CG_UI\View\Filters\FilterOptionsInterface;
use CG_UI\View\Filters\Options\Select;

class OrdersTableTagColumns implements OrdersTableModifierInterface, FilterOptionsInterface
{
    protected $di;
    protected $activeUserContainer;
    protected $tagClient;
    protected $javascript;

    public function __construct(
        Di $di,
        ActiveUserInterface $activeUserContainer,
        TagStorage $tagClient,
        ViewModel $javascript
    )
    {
        $this
            ->setDi($di)
            ->setActiveUserContainer($activeUserContainer)
            ->setTagClient($tagClient);
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

    public function setJavascript(ViewModel $javascript)
    {
        $this->javascript = $javascript;
        return $this;
    }

    /**
     * @return ViewModel
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    protected function getActiveUserTags()
    {
        return $this->getTagClient()->fetchCollectionAll(
            1,
            'all',
            $this->getActiveUser()->getOuList(),
            []
        );
    }

    /**
     * return Select[] array of options to be added to filter
     */
    public function getOptions()
    {
        $options = [];

        try {
            $tags = $this->getActiveUserTags();
            foreach ($tags as $tag) {
                $options[] = $this->getDi()->get(
                    Select::class,
                    [
                        'title' => htmlentities($tag->getTag(), ENT_QUOTES),
                    ]
                );
            }
        } catch (NotFound $exception) {
            // No Tags -- Nothing to do
        }

        return $options;
    }

    public function modifyTable(DataTable $ordersTable)
    {
        try {
            $tags = $this->getActiveUserTags();

            foreach ($tags as $tag) {
                $this->addTagColumn($ordersTable, $tag);
            }

            $ordersTable->addChild(
                $this->getJavascript(),
                'javascript',
                true
            );
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
                'defaultContent' => '',
                'visible' => false
            ]
        );

        $ordersTable->addColumn($column);
    }
}