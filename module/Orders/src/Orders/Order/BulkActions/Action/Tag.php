<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG\Order\Shared\Tag\StorageInterface;
use CG\Order\Shared\Tag\Entity;
use Orders\Order\BulkActions\SubAction\Tag as SubAction;
use CG\Stdlib\Exception\Runtime\NotFound;

class Tag extends Action
{
    protected $activeUserContainer;
    protected $storage;
    protected $urlView;
    protected $prototypeSubAction;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        StorageInterface $storage,
        ViewModel $urlView,
        SubAction $prototypeSubAction,
        array $elementData = [],
        ViewModel $javascript = null
    )
    {
        parent::__construct('sprite-tag-22-black', 'Tag', 'tag', $elementData, $javascript);
        $this
            ->setActiveUserContainer($activeUserContainer)
            ->setStorage($storage)
            ->setUrlView($urlView)
            ->setPrototypeSubAction($prototypeSubAction)
            ->configure();
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

    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        $this->urlView->setVariables(
            [
                'route' => 'Orders/tag/action',
                'parameters' => [
                    'tagAction' => '{{action}}'
                ],
                'mustache' => true
            ]
        );
        return $this;
    }

    /**
     * @return ViewModel;
     */
    public function getUrlView()
    {
        return $this->urlView;
    }

    public function setPrototypeSubAction(SubAction $prototypeSubAction)
    {
        $this->prototypeSubAction = $prototypeSubAction;
        return $this;
    }

    /**
     * @return SubAction
     */
    public function getPrototypeSubAction()
    {
        return $this->prototypeSubAction;
    }

    protected function configure()
    {
        $this->addElementView($this->getUrlView());
        $this->createSubActions();
        return $this;
    }

    protected function createSubActions()
    {
        try {
            $tags = $this->getStorage()->fetchCollectionAll(
                1,
                'all',
                $this->getActiveUser()->getOuList(),
                []
            );

            foreach ($tags as $tag) {
                $this->addSubAction(
                    $this->createSubAction($tag)
                );
            }
        } catch (NotFound $exception) {
            // No Tags -- Nothing to do
        }
    }

    protected function createSubAction(Entity $tag)
    {
        $subAction = clone $this->getPrototypeSubAction();
        $subAction->setTitle(htmlentities($tag->getTag(), ENT_QUOTES));
        $subAction->setElementData($this->getElementData(), false);
        $subAction->addElementData('tag', $tag->getTag());
        $subAction->addElementView($this->getUrlView());
        $subAction->setJavascript($this->getJavascript());
        return $subAction;
    }
}