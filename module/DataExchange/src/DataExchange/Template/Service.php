<?php
namespace DataExchange\Template;

use CG\DataExchangeTemplate\Entity as Template;
use CG\DataExchangeTemplate\Filter as TemplateFilter;
use CG\DataExchangeTemplate\Mapper as TemplateMapper;
use CG\DataExchangeTemplate\Service as TemplateService;
use CG\OrganisationUnit\Entity as Ou;
use CG\OrganisationUnit\Service as OuService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'TemplateService';
    protected const LOG_OU_NOT_FOUND_MSG = 'OU %d has not been found';

    /** @var TemplateService */
    protected $templateService;
    /** @var TemplateMapper */
    protected $templateMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var OuService */
    protected $ouService;

    public function __construct(
        TemplateService $templateService,
        TemplateMapper $templateMapper,
        ActiveUserInterface $activeUserContainer,
        OuService $ouService
    ){
        $this->templateService = $templateService;
        $this->templateMapper = $templateMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->ouService = $ouService;
    }

    public function fetchAllTemplatesForActiveUser(string $type, Ou $ou): array
    {
        try {
            $filter = $this->buildTemplateFilter($ou->getId(), $type);
            $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
            $templatesArray = [];
            /** @var Template $template */
            foreach ($templateCollection as $template) {
                $templatesArray[] = $template->toArray();
            }
            return $templatesArray;
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
        $template = $this->fetchTemplateByTypeAndId($type, $id);
        $this->templateService->remove($template);
    }

    public function fetchOrganisationUnit(): Ou
    {
        try {
            $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            return $this->ouService->fetch($rootOuId);
        } catch (NotFound $exception) {
            $this->logAlertException($exception, static::LOG_OU_NOT_FOUND_MSG, [$rootOuId ?? 0], static::LOG_CODE);
            throw $exception;
        }
    }

    protected function fetchTemplateByTypeAndId(string $type, int $id): Template
    {
        $filter = (new TemplateFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setType([$type])
            ->setId([$id]);

        $templateCollection = $this->templateService->fetchCollectionByFilter($filter);
        return $templateCollection->getFirst();
    }

    protected function saveNewTemplate(string $type, array $templateArray): Template
    {
        $templateArray = array_merge($templateArray, [
            'type' => $type,
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
        ]);

        $template = $this->templateMapper->fromArray($templateArray);
        $this->templateService->save($template);
        return $this->templateService->fetch($template->getId());
    }

    protected function updateExistingTemplate(string $type, array $templateArray, int $templateId): Template
    {
        /** @var Template $existingTemplate */
        $existingTemplate = $this->fetchTemplateByTypeAndId($type, $templateId);
        $updatedTemplate = $this->templateMapper->fromArray(
            array_merge($existingTemplate->toArray(), $templateArray)
        );
        $updatedTemplate->setStoredETag($templateArray['etag'] ?? $existingTemplate->getETag());
        $this->templateService->save($updatedTemplate);
        return $this->templateService->fetch($updatedTemplate->getId());
    }
}
