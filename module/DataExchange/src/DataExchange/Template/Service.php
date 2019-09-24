<?php
namespace DataExchange\Template;

use CG\DataExchangeTemplate\Entity as Template;
use CG\DataExchangeTemplate\Filter as TemplateFilter;
use CG\DataExchangeTemplate\Mapper as TemplateMapper;
use CG\DataExchangeTemplate\Service as TemplateService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var TemplateService */
    protected $templateService;
    /** @var TemplateMapper */
    protected $templateMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        TemplateService $templateService,
        TemplateMapper $templateMapper,
        ActiveUserInterface $activeUserContainer
    ){
        $this->templateService = $templateService;
        $this->templateMapper = $templateMapper;
        $this->activeUserContainer = $activeUserContainer;
    }

    public static function getCgFieldOptions(): array
    {
        return [
            'SKU',
            'Product Name',
            'Total Stock',
            'Cost Price'
        ];
    }

    public function fetchAllTemplatesForActiveUser(): array
    {
        try {
            $filter = $this->buildTemplateFilter($this->activeUserContainer->getActiveUserRootOrganisationUnitId());
            $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
            return $templateCollection->toArray();
        } catch (NotFound $exception) {
            return [];
        }
    }

    protected function buildTemplateFilter(int $ouId): TemplateFilter
    {
        return (new TemplateFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType([Template::TYPE_STOCK])
            ->setOrganisationUnitId([$ouId]);
    }

    public function saveForActiveUser(array $templateArray, ?int $templateId = null): Template
    {
        if (!$templateId) {
            return $this->saveNewTemplate($templateArray);
        }

        return $this->updateExistingTemplate($templateArray, $templateId);
    }

    public function remove(int $id): void
    {
        $this->templateService->removeById($id);
    }

    protected function saveNewTemplate(array $templateArray): Template
    {
        $template = $this->templateMapper->fromArray($templateArray);
        $this->setTypeAndOuIdOnTemplate($template);
        return $this->templateService->save($template);
    }

    protected function updateExistingTemplate(array $templateArray, int $templateId): Template
    {
        /** @var Template $existingTemplate */
        $existingTemplate = $this->templateService->fetch($templateId);
        $updatedTemplate = $this->templateMapper->fromArray(
            array_merge($existingTemplate->toArray(), $templateArray)
        );
        $this->setTypeAndOuIdOnTemplate($updatedTemplate);
        $updatedTemplate->setStoredETag($templateArray['etag'] ?? $existingTemplate->getETag());
        return $this->templateService->save($updatedTemplate);
    }

    protected function setTypeAndOuIdOnTemplate(Template $template): void
    {
        $template
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUserRootOrganisationUnitId())
            ->setType(Template::TYPE_STOCK);
    }
}
