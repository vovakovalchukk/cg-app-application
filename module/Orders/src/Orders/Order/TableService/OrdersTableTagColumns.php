<?php
namespace Orders\Order\TableService;

use CG\Order\Shared\Tag\Entity as Tag;
use CG\Order\Service\Filter as Filter;
use CG\Order\Shared\Tag\StorageInterface as TagStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\DataTable;
use CG_UI\View\Filters\SelectOptionsInterface;
use Zend\Di\Di;
use Zend\View\Model\ViewModel;

class OrdersTableTagColumns implements OrdersTableModifierInterface, SelectOptionsInterface
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
    ) {
        $this->di = $di;
        $this->activeUserContainer = $activeUserContainer;
        $this->tagClient = $tagClient;
        $this->javascript = $javascript;
    }

    public function getActiveUser(): User
    {
        return $this->activeUserContainer->getActiveUser();
    }

    /**
     * @return Tag[]
     */
    protected function getActiveUserTags()
    {
        return $this->tagClient->fetchCollectionAll(
            1,
            'all',
            $this->getActiveUser()->getOuList(),
            []
        );
    }

    /**
     * {@inherit}
     */
    public function getSelectOptions()
    {
        $options = [Filter::TAGS_ANY => Filter::TAGS_ANY];

        try {
            $tags = $this->getActiveUserTags();
            foreach ($tags as $tag) {
                $options[$tag->getTag()] = $tag->getTag();
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
                $this->javascript,
                'javascript',
                true
            );
        } catch (NotFound $exception) {
            // No Tags -- Nothing to do
        }
    }

    protected function addTagColumn(DataTable $ordersTable, Tag $tag)
    {
        $viewModel = $this->di->newInstance(
            ViewModel::class,
            [
                'variables' => [
                    'id' => $ordersTable->getVariable('id'),
                    'tag' => htmlentities($tag->getTag(), ENT_QUOTES)
                ],
                'template' => 'orders/orders/table/header/tag'
            ]
        );

        /** @var DataTable\Column $column */
        $column = $this->di->newInstance(
            DataTable\Column::class,
            [
                'column' => base64_encode($tag->getTag()),
                'class' => 'user-created-tag-col',
                'templateId' => 'custom-tag',
                'viewModel' => $viewModel,
                'defaultContent' => '',
                'visible' => false
            ]
        );

        $ordersTable->addColumn($column);
    }
}