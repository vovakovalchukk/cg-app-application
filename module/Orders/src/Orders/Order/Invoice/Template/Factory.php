<?php
namespace Orders\Order\Invoice\Template;

use Zend\Di\Di;
use CG\Order\Shared\Entity as Order;
use CG\Template\Service as TemplateService;
use CG\Template\Entity;
use CG\Template\InvoiceEntity;
use CG\Template\Element\Text;
use CG\Template\FontFamily;

class Factory
{
    protected $di;
    protected $templateService;

    public function __construct(Di $di, TemplateService $templateService)
    {
        $this->setDi($di)
             ->setTemplateService($templateService);
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
     * @return Template
     */
    public function getTemplateById($templateId)
    {
        return $this->getTemplateService()->fetch($templateId);
    }

    /**
     * @param array $templateConfig
     * @return Template
     */
    public function getTemplateForOrderEntity($templateConfig)
    {
        return $this->getDi()->get(
            Entity::class, $templateConfig
        );
    }

    /**
     * @return Di
     */
    protected function getDi()
    {
        return $this->di;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return TemplateService
     */
    protected function getTemplateService()
    {
        return $this->templateService;
    }

    public function setTemplateService(TemplateService $templateService)
    {
        $this->templateService = $templateService;
        return $this;
    }
}