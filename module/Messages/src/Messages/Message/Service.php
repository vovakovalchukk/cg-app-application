<?php
namespace Messages\Message;

use CG\Communication\Message\Mapper as MessageMapper;
use CG\Communication\Message\ReplyFactory as MessageReplyFactory;
use CG\Communication\Message\Service as MessageService;
use CG\Communication\Thread\Service as ThreadService;
use CG\Intercom\Event\Request as IntercomEvent;
use CG\Intercom\Event\Service as IntercomEventService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\User\OrganisationUnit\Service as UserOuService;
use CG_UI\View\Helper\DateFormat;

class Service
{
    use FormatMessageDataTrait;

    protected const EVENT_REPLY_SENT = 'Message Reply Sent';

    /** @var MessageService */
    protected $messageService;
    /** @var MessageMapper */
    protected $messageMapper;
    /** @var ThreadService */
    protected $threadService;
    /** @var UserOuService */
    protected $userOuService;
    /** @var MessageReplyFactory */
    protected $messageReplyFactory;
    /** @var IntercomEventService */
    protected $intercomEventService;
    /** @var DateFormat */
    protected $dateFormatter;

    public function __construct(
        MessageService $messageService,
        MessageMapper $messageMapper,
        ThreadService $threadService,
        UserOuService $userOuService,
        MessageReplyFactory $messageReplyFactory,
        IntercomEventService $intercomEventService,
        DateFormat $dateFormatter
    ) {
        $this->messageService = $messageService;
        $this->messageMapper = $messageMapper;
        $this->threadService = $threadService;
        $this->userOuService = $userOuService;
        $this->messageReplyFactory = $messageReplyFactory;
        $this->intercomEventService = $intercomEventService;
        $this->dateFormatter = $dateFormatter;
    }

    public function createMessageForThreadForActiveUser($threadId, $body): array
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

    protected function notifyOfReply(): void
    {
        $user = $this->userOuService->getActiveUser();
        $event = new IntercomEvent(static::EVENT_REPLY_SENT, $user->getId());
        $this->intercomEventService->save($event);
    }

    // Required by FormatMessageDataTrait
    protected function getDateFormatter()
    {
        return $this->dateFormatter;
    }
}
