<?php
namespace Orders\Order\Invoice\Renderer;

use CG\Template\RendererInterface;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity as Template;

interface ServiceInterface
{
    /**
     * @return RendererInterface
     */
    public function getRenderer();
    public function renderOrderTemplate(Order $order, Template $template);
    public function combine(array $renderedContent);
}