<?php
namespace Orders\Order\Invoice\Template;

use CG\Template\Storage\ObjectFactory as Storage;
use CG\User\ActiveUserInterface;
use Zend\Di\Di;

class ObjectStorage extends Storage
{
    protected $activeUserContainer;

    public function __construct(Di $di, ActiveUserInterface $activeUserContainer)
    {
        parent::__construct($di);
        $this->setActiveUserContainer($activeUserContainer);
    }

    protected function getTemplateOU()
    {
        return $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId();
    }

    /**
     * @return self
     */
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
} 
