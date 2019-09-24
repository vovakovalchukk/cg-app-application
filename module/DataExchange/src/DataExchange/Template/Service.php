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
    const CG_FIELDS_STOCK = [
        'SKU',
        'Product Name',
        'Total Stock',
        'Cost Price'
    ];

    const CG_FIELDS_ORDERS = [
        'SKU',
        'Product Name',
        'Total Stock',
        'Cost Price'
    ];

    const CG_FIELDS_MAP_BY_TYPE = [
        Template::TYPE_STOCK => self::CG_FIELDS_STOCK,
        Template::TYPE_ORDER => self::CG_FIELDS_ORDERS,
    ];

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

    public static function getCgFieldOptionsByType(string $type): array
    {
        return self::CG_FIELDS_MAP_BY_TYPE[$type];
    }

    public function fetchAllTemplatesForActiveUser(string $type): array
    {
        try {
            $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $filter = $this->buildTemplateFilter($ouId, $type);
            $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
            return $templateCollection->toArray();
        } catch (NotFound $exception) {
            return [];
        }
    }

    protected function buildTemplateFilter(int $ouId, string $type): TemplateFilter
    {
        return (new TemplateFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType([$type])
            ->setOrganisationUnitId([$ouId]);
    }

    public function saveForActiveUser(string $type, array $templateArray, ?int $templateId = null): Template
    {
        if (!$templateId) {
            return $this->saveNewTemplate($type, $templateArray);
        }

        return $this->updateExistingTemplate($type, $templateArray, $templateId);
    }

    public function remove(string $type, int $id): void
    {
        $filter = (new TemplateFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setType([$type])
            ->setId([$id]);

        $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
        $this->templateService->remove($templateCollection->getFirst());
    }

    protected function saveNewTemplate(string $type, array $templateArray): Template
    {
        $template = $this->templateMapper->fromArray($templateArray);
        $this->setTypeAndOuIdOnTemplate($type, $template);
        return $this->templateService->save($template);
    }

    protected function updateExistingTemplate(string $type, array $templateArray, int $templateId): Template
    {
        /** @var Template $existingTemplate */
        $existingTemplate = $this->templateService->fetch($templateId);
        $updatedTemplate = $this->templateMapper->fromArray(
            array_merge($existingTemplate->toArray(), $templateArray)
        );
        $this->setTypeAndOuIdOnTemplate($type, $updatedTemplate);
        $updatedTemplate->setStoredETag($templateArray['etag'] ?? $existingTemplate->getETag());
        return $this->templateService->save($updatedTemplate);
    }

    protected function setTypeAndOuIdOnTemplate(string $type, Template $template): void
    {
        $template
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUserRootOrganisationUnitId())
            ->setType($type);
    }
}
