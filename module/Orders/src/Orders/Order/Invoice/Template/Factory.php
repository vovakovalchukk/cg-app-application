<?php
namespace Orders\Order\Invoice\Template;

use Zend\Di\Di;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity as Template;
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
    public function getTemplateForOrderEntity(Order $order)
    {
        return $this->getDi()->get(
            Template::class,
            [
                'type' => 'Invoice',
                'organisationUnitId' => 0,
                'minHeight' => 842,
                'minWidth' => 592,
                'elements' => [
                    new Text(12, new FontFamily\Courier(), 'black', 'Invoice', 0, 0),
                    (new Text(12, new FontFamily\Courier(), 'green', '{{order.id}}', 0, 0))
                        ->setY(25)
                ],
                'id' => 0
            ]
        );
    }
}