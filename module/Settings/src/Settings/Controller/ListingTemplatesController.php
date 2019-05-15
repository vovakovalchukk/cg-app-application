<?php

namespace Settings\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\ListingTemplate\Service as ListingTemplateService;

class ListingTemplatesController extends AbstractActionController
{
    const ROOT_INDEX = 'Templates';
    const SAVE_INDEX = 'Save';
    const DELETE_INDEX = 'Delete';

    const ROUTE_INDEX_URI = '/templates';
    const ROUTE_SAVE_URI = '/save';
    const ROUTE_DELETE_URI = '/delete';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    protected $listingTemplateService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ListingTemplateService $listingTemplateService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->listingTemplateService = $listingTemplateService;
    }

    public function indexAction()
    {
        $view = $this->newViewModel();
        $view->setTemplate('settings/listing/index');
        $view->setVariable('listingTemplateTags', $this->getListingTemplateTags());
        $view->setVariable('templates', $this->getUsersTemplates());
        $view->setVariable('isHeaderBarVisible', true);
        $view->setVariable('subHeaderHide', true);

        return $view;
    }

    public function deleteAction()
    {
        $response = $this->newJsonModel();
        $response->setVariable('success', [
            "message" => "You have successfully deleted your template."
        ]);
        return $response;
    }

    public function saveAction()
    {
        // todo - replace with non dummy data as part of TAC-433
        $response = $this->newJsonModel();
        $response->setVariable('success', [
           "message" => "You have successfully saved your template."
        ]);
        return $response;
    }

    protected function newJsonModel()
    {
        return $this->jsonModelFactory->newInstance();
    }

    protected function newViewModel($variables = null, $options = null)
    {
        return $this->viewModelFactory->newInstance($variables, $options);
    }

    protected function getListingTemplateTags()
    {
        $templateTags = $this->listingTemplateService->getListingTemplateTags();
        return json_encode($templateTags);
    }

    protected function getUsersTemplates()
    {
        $usersTemplates = $this->listingTemplateService->getUsersTemplates();
        return json_encode($usersTemplates, JSON_UNESCAPED_SLASHES);
    }
}
