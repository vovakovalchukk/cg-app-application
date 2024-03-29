<?php
namespace Messages\Message\Template;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Type as ChannelType;
use CG\Communication\Message\Template\Collection as MessageTemplateCollection;
use CG\Communication\Message\Template\Entity as MessageTemplate;
use CG\Communication\Message\Template\Filter as MessageTemplateFilter;
use CG\Communication\Message\Template\Mapper as MessageTemplateMapper;
use CG\Communication\Message\Template\Service as MessageTemplateService;
use CG\Communication\Thread\Entity as Thread;
use CG\Communication\Thread\Mapper as ThreadMapper;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Stdlib\DateTime as CGDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Template\ReplaceManager\MessageContent as MessageContentTagReplacer;
use CG\User\ActiveUserInterface;
use CG\Template\Element\SimpleString as SimpleStringElement;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var MessageTemplateMapper */
    protected $messageTemplateMapper;
    /** @var MessageTemplateService */
    protected $messageTemplateService;
    /** @var AccountService */
    protected $accountService;
    /** @var MessageContentTagReplacer */
    protected $messageContentTagReplacer;
    /** @var ThreadMapper */
    protected $threadMapper;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        MessageTemplateMapper $messageTemplateMapper,
        MessageTemplateService $messageTemplateService,
        AccountService $accountService,
        MessageContentTagReplacer $messageContentTagReplacer,
        ThreadMapper $threadMapper
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->messageTemplateMapper = $messageTemplateMapper;
        $this->messageTemplateService = $messageTemplateService;
        $this->accountService = $accountService;
        $this->messageContentTagReplacer = $messageContentTagReplacer;
        $this->threadMapper = $threadMapper;
    }

    public function fetchAllForActiveOuAsArray(): array
    {
        try {
            $collection = $this->fetchForActiveOu();
            $array = [];
            /** @var MessageTemplate $entity */
            foreach ($collection as $entity) {
                $array[] = $entity->toArray();
            }
            return $array;
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function fetchForActiveOu(): MessageTemplateCollection
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = (new MessageTemplateFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$rootOuId]);
        return $this->messageTemplateService->fetchCollectionByFilter($filter);
    }

    public function getTemplateTagOptions(): array
    {
        $tagOptions = [];
        foreach ($this->messageContentTagReplacer->getAvailableTags() as $name => $value) {
            $tagOptions[] = ['name' => $name, 'value' => $value];
        }
        return $tagOptions;
    }

    public function fetchAllSalesAccountsForActiveOuAsOptions(): array
    {
        try {
            $accounts = $this->fetchAllSalesAccountsForActiveOu();
            $options = [];
            /** @var Account $account */
            foreach ($accounts as $account) {
                $options[] = [
                    'name' => $account->getDisplayName(),
                    'value' => $account->getId()
                ];
            }
            return $options;
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function fetchAllSalesAccountsForActiveOu(): AccountCollection
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setType(ChannelType::SALES)
            ->setRootOrganisationUnitId([$rootOuId]);
        return $this->accountService->fetchByFilter($filter);
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

    public function renderPreview(string $template, ?int $accountId = null): string
    {
        if ($accountId) {
            $account = $this->accountService->fetch($accountId);
        } else {
            $account = $this->fetchAllSalesAccountsForActiveOu()->getFirst();
        }
        $thread = $this->getExampleThread($account);
        $element = new SimpleStringElement($template);
        $element = $this->messageContentTagReplacer->replaceTagsOnElementForThread($element, $thread);
        return $element->getReplacedText();
    }

    protected function getExampleThread(Account $account): Thread
    {
        $date = new CGDateTime();
        return $this->threadMapper->fromArray([
            'id' => 'example',
            'channel' => $account->getChannel(),
            'organisationUnitId' => $account->getOrganisationUnitId(),
            'accountId' => $account->getId(),
            'status' => ThreadStatus::AWAITING_REPLY,
            'created' => $date->stdFormat(),
            'updated' => $date->stdFormat(),
            'name' => 'John Doe',
            'externalUsername' => 'john.doe@example.com',
            'assignedUserId' => null,
            'subject' => 'Example subject',
            'externalId' => 'example'
        ]);
    }
}