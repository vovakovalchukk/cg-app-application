<?php
namespace Messages\Message\Template;

use CG\Communication\Message\Template\Entity as MessageTemplate;
use CG\Communication\Message\Template\Mapper as MessageTemplateMapper;
use CG\Communication\Message\Template\Service as MessageTemplateService;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var MessageTemplateMapper */
    protected $messageTemplateMapper;
    /** @var MessageTemplateService */
    protected $messageTemplateService;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        MessageTemplateMapper $messageTemplateMapper,
        MessageTemplateService $messageTemplateService
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->messageTemplateMapper = $messageTemplateMapper;
        $this->messageTemplateService = $messageTemplateService;
    }

    public function saveForActiveUser(array $data): MessageTemplate
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $data['organisationUnitId'] = $rootOuId;
        if (!isset($data['id'])) {
            return $this->saveNew($data);
        }
        return $this->saveExisting($data);
    }

    protected function saveNew(array $data): MessageTemplate
    {
        $entity = $this->messageTemplateMapper->fromArray($data);
        return $this->save($entity);
    }

    protected function saveExisting(array $data): MessageTemplate
    {
        /** @var MessageTemplate $fetchedEntity */
        $fetchedEntity = $this->messageTemplateService->fetch($data['id']);
        $entityArray = array_merge($fetchedEntity->toArray(), $data);
        $updatedEntity = $this->messageTemplateMapper->fromArray($entityArray);
        $updatedEntity->setStoredETag($data['etag'] ?? $fetchedEntity->getStoredETag());
        return $this->save($updatedEntity);
    }

    protected function save(MessageTemplate $entity): MessageTemplate
    {
        $entityHal = $this->messageTemplateService->save($entity);
        $entity = $this->messageTemplateMapper->fromHal($entityHal);
        if (!$entity->getStoredETag()) {
            return $this->messageTemplateService->fetch($entity->getId());
        }
        return $entity;
    }

    public function remove(int $id): void
    {
        $entity = $this->messageTemplateService->fetch($id);
        $this->messageTemplateService->remove($entity);
    }
}