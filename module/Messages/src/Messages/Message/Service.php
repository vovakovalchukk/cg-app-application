<?php
namespace Messages\Message;

use CG\Communication\Message\Mapper as MessageMapper;
use CG\Communication\Message\ReplyFactory as MessageReplyFactory;
use CG\Communication\Message\Service as MessageService;
use CG\Communication\Thread\Service as ThreadService;
use CG\Communication\Thread\Status as ThreadStatus;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\User\OrganisationUnit\Service as UserOuService;
use Messages\Message\FormatMessageDataTrait;

class Service
{
    use FormatMessageDataTrait;

    const EVENT_REPLY_SENT = 'Message Reply Sent';

    protected $messageService;
    protected $messageMapper;
    protected $threadService;
    protected $userOuService;
    protected $messageReplyFactory;
    protected $intercomEventService;

    public function __construct(
        MessageService $messageService,
        MessageMapper $messageMapper,
        ThreadService $threadService,
        UserOuService $userOuService,
        MessageReplyFactory $messageReplyFactory,
        IntercomEventService $intercomEventService
    ) {
        $this->setMessageService($messageService)
            ->setMessageMapper($messageMapper)
            ->setThreadService($threadService)
            ->setUserOuService($userOuService)
            ->setMessageReplyFactory($messageReplyFactory)
            ->setIntercomEventService($intercomEventService);
    }

    public function createMessageForThreadForActiveUser($threadId, $body)
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

        $this->notifyOfReply();
        return $this->formatMessageData($message, $thread);
    }

    protected function notifyOfReply()
    {
        $user = $this->userOuService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_REPLY_SENT, $user->getId());
        $this->intercomEventService->save($event);
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

    protected function setIntercomEventService(IntercomEventService $intercomEventService)
    {
        $this->intercomEventService = $intercomEventService;
        return $this;
    }
}
