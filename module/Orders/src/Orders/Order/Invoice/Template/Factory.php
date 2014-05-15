<?php
namespace Orders\Order\Invoice\Template;

use Zend\Di\Di;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity;
use CG\Template\Element\Text;
use CG\Template\FontFamily;

class Factory
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
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

    /**
     * @param Order $order
     * @return Template
     */
    public function getTemplateForOrderEntity($templateConfig)
    {
        return $this->getDi()->get(
            Entity::class, $templateConfig
        );
    }
}