<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Template\Service as TemplateService;
use CG\User\OrganisationUnit\Service as UserOrganisationUnitService;
use CG\Zend\Stdlib\Mvc\Model\Helper\Translate;

class InvoiceController extends AbstractActionController
{
    const ROUTE = 'Invoice';

    protected $viewModelFactory;
    protected $templateService;
    protected $userOrganisationUnitService;
    protected $translate;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        TemplateService $templateService,
        UserOrganisationUnitService $userOrganisationUnitService,
        Translate $translate
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setTemplateService($templateService)
            ->setUserOrganisationUnitService($userOrganisationUnitService)
            ->setTranslate($translate);
    }

    public function designAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->addChild($this->getTemplateSelectView(), 'templates');
        return $view;
    }

    protected function getTemplateSelectView()
    {
        $organisationUnitIds = $this->getUserOrganisationUnitService()->getAncestorOrganisationUnitIdsByActiveUser();
        $templates = $this->getTemplateService()->fetchInvoiceCollectionByOrganisationUnitWithHardCoded($organisationUnitIds);
        $options = [];
        foreach ($templates as $template) {
            $options[] = [
                "title" => $template->getName(),
                "value" => $template->getId(),
            ];
        }
        $templateView = $this->getViewModelFactory()->newInstance(["options" => $options]);
        $templateView->setTemplate('elements/custom-select.mustache');
        $templateView->setVariable('name', 'template');
        $templateView->setVariable('initialTitle', $this->getTranslate()->translate('Select Template'));
        return $templateView;
    }

    public function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    public function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    public function setTemplateService(TemplateService $templateService)
    {
        $this->templateService = $templateService;
        return $this;
    }

    public function getTemplateService()
    {
        return $this->templateService;
    }

    public function setUserOrganisationUnitService(UserOrganisationUnitService $userOrganisationUnitService)
    {
        $this->userOrganisationUnitService = $userOrganisationUnitService;
        return $this;
    }

    public function getUserOrganisationUnitService()
    {
        return $this->userOrganisationUnitService;
    }

    public function setTranslate(Translate $translate)
    {
        $this->translate = $translate;
        return $this;
    }

    public function getTranslate()
    {
        return $this->translate;
    }
}
