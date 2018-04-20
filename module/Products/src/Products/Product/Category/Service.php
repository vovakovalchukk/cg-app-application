<?php
namespace Products\Product\Category;

use CG\Product\Category\Template\Filter as CategoryTemplateFilter;
use CG\Product\Category\Template\StorageInterface as CategoryTemplateStorage;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\OrganisationUnit\Service as OUService;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var OUService */
    protected $ouService;
    /** @var CategoryTemplateStorage */
    protected $categoryTemplateStorage;

    public function __construct(
        ActiveUserInterface $activeUser,
        OUService $ouService,
        CategoryTemplateStorage $categoryTemplateStorage
    ) {
        $this->activeUser = $activeUser;
        $this->ouService = $ouService;
        $this->categoryTemplateStorage = $categoryTemplateStorage;
    }

    public function getTemplateOptions(): array
    {
        try {
            $categoryTemplates = $this->categoryTemplateStorage->fetchCollectionByFilter(
                (new CategoryTemplateFilter('all', 1))
                    ->setOrganisationUnitId(
                        $this->ouService->fetchRelatedOrganisationUnitIds($this->activeUser->getActiveUserRootOrganisationUnitId())
                    )
            );
        } catch (NotFound $exception) {
            return [];
        }

        $templateOptions = [];
        /** @var CategoryTemplate $categoryTemplate */
        foreach ($categoryTemplates as $categoryTemplate) {
            $templateOptions[$categoryTemplate->getId()] = $categoryTemplate->getName();
        }
        return $templateOptions;
    }
}