<?php
namespace Orders\Order\BulkActions;

use CG_UI\View\BulkActions\Action;
use CG_UI\View\BulkActions\SubAction;
use CG_UI\View\BulkActions\ActionModifierInterface;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Order\Shared\Tag\StorageInterface;
use Zend\Di\Di;
use CG\Stdlib\Exception\Runtime\NotFound;

class TagActionModifier implements ActionModifierInterface
{
    protected $activeUserContainer;
    protected $storage;
    protected $di;

    public function __construct(ActiveUserInterface $activeUserContainer, StorageInterface $storage, Di $di)
    {
        $this->setActiveUserContainer($activeUserContainer)->setStorage($storage)->setDi($di);
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

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    public function setDi($di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di;
     */
    public function getDi()
    {
        return $this->di;
    }

    public function apply(Action $action)
    {
        try {
            $tags = $this->getStorage()->fetchCollectionAll(
                1,
                'all',
                $this->getActiveUser()->getAvailableOrganisationUnitIds(),
                []
            );

            foreach ($tags as $tag) {
                $javascript = $this->getDi()->newInstance('TagJavascript');
                $javascript->setVariable('tag', $tag->getTag());

                $subAction = $this->getDi()->newInstance(
                    SubAction::class,
                    [
                        'title' => $tag->getTag(),
                        'action' => 'tag-' . $tag->getTag(),
                        'javascript' => $javascript
                    ]
                );

                $action->addSubAction($subAction);
            }
        } catch (NotFound $exception) {
            // No Tags -- Nothing to do
        }
    }
}