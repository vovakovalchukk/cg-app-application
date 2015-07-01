<?php
namespace Messages\Message;

use CG\Communication\Message\Mapper as MessageMapper;
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

    public function __construct(
        MessageService $messageService,
        MessageMapper $messageMapper,
        ThreadService $threadService,
        UserOuService $userOuService
    ) {
        $this->setMessageService($messageService)
            ->setMessageMapper($messageMapper)
            ->setThreadService($threadService)
            ->setUserOuService($userOuService);
    }

    public function createMessageForThreadForActiveUser($threadId, $body, $resolve = false)
    {
        $thread = $this->threadService->fetch($threadId);
        $user = $this->userOuService->getActiveUser();
        $data = [
            'threadId' => $threadId,
            'body' => $body,
            'created' => (new StdlibDateTime())->stdFormat(),
            'organisationUnitId' => $thread->getOrganisationUnitId(),
            'accountId' => $thread->getAccountId(),
            'name' => $user->getFirstName(). ' ' . $user->getLastName(),
            'externalUsername' => $user->getUsername(),
        ];
// TODO: call out to the channel library to send the message (passing along the $resolve flag)
// and return it's ID, then we can save it to the API. Requires CGIV-4698
$data['id'] = 'TEST'; 
        $message = $this->messageMapper->fromArray($data);
//        $message = $this->messageService->save($message);
        if ($thread->getStatus() == ThreadStatus::NEW_THREAD) {
            $thread->setStatus(ThreadStatus::AWAITING_REPLY);
            $this->threadService->save($thread);
        }
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
}
