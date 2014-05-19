<?php
namespace Orders\Order\Invoice\Template;

use Zend\Di\Di;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity;
use CG\Template\InvoiceEntity;
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
     * @return Template
     */
    public function getDefaultTemplateForOrderEntity($organisationUnitId)
    {
        return $this->getDi()->get(
            InvoiceEntity::class, compact('organisationUnitId')
        );
    }

    /**
     * @param array $templateConfig
     * @return Template
     */
    public function getTemplateForOrderEntity($templateConfig)
    {
//        echo "<pre>";
//        print_r($templateConfig);
//        exit;

        return $this->getDi()->get(
            Entity::class, $templateConfig
        );
    }
}