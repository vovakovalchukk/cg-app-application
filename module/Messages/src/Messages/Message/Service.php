<?php
namespace Messages\Message;

use CG\Communication\Message\Mapper as MessageMapper;
use CG\Communication\Message\ReplyFactory as MessageReplyFactory;
use CG\Communication\Message\Service as MessageService;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\User\OrganisationUnit\Service as UserOuService;
use Messages\Message\FormatMessageDataTrait;

class Service
{
    use FormatMessageDataTrait;

    protected $messageService;
    protected $messageMapper;
    protected $threadService;
    protected $userOuService;
    protected $messageReplyFactory;

    public function __construct(
        MessageService $messageService,
        MessageMapper $messageMapper,
        ThreadService $threadService,
        UserOuService $userOuService,
        MessageReplyFactory $messageReplyFactory
    ) {
        $this->setMessageService($messageService)
            ->setMessageMapper($messageMapper)
            ->setThreadService($threadService)
            ->setUserOuService($userOuService)
            ->setMessageReplyFactory($messageReplyFactory);
    }

    public function createMessageForThreadForActiveUser($threadId, $body, $resolve = false)
    {
        $thread = $this->threadService->fetch($threadId);
        $user = $this->userOuService->getActiveUser();
        $data = [
            'id' => 'TEMP', // Required by the mapper but will be replaced by the reply factory
            'threadId' => $threadId,
            'body' => $body,
            'created' => (new StdlibDateTime())->stdFormat(),
            'organisationUnitId' => $thread->getOrganisationUnitId(),
            'accountId' => $thread->getAccountId(),
            'name' => $user->getFirstName(). ' ' . $user->getLastName(),
            'externalUsername' => $user->getUsername(),
        ];
        $message = $this->messageMapper->fromArray($data);
        $this->messageReplyFactory->sendReply($message, $thread);

        return $this->formatMessageData($message, $thread);
    }

    protected function setMessageService(MessageService $messageService)
    {
        $this->messageService = $messageService;
        return $this;
    }

    protected function setMessageMapper(MessageMapper $messageMapper)
    {
        $this->messageMapper = $messageMapper;
        return $this;
    }

    protected function setThreadService(ThreadService $threadService)
    {
        $this->threadService = $threadService;
        return $this;
    }

    protected function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }

    protected function setMessageReplyFactory(MessageReplyFactory $messageReplyFactory)
    {
        $this->messageReplyFactory = $messageReplyFactory;
        return $this;
    }
}
