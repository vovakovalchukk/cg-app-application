<?php
namespace Messages\Message\Attachment;

use CG\Communication\Message\Attachment\Collection as AttachmentCollection;
use CG\Communication\Message\Attachment\Entity as Attachment;
use CG\Communication\Message\Attachment\Filter as AttachmentFilter;
use CG\Communication\Message\Attachment\Service as AttachmentService;
use CG\Communication\Thread\Collection as ThreadCollection;
use CG\Communication\Thread\Entity as Thread;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    protected const FETCH_LIMIT = 200;

    /** @var AttachmentService */
    protected $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function fetchAttachmentsForThreads(ThreadCollection $threads): AttachmentCollection
    {
        $messageIds = $this->buildMessageIdsFromThreads($threads);
        $filter = $this->buildAttachmentFilter($messageIds);
        return $this->fetchAttachments($filter);
    }

    public function fetchAttachmentsForThread(Thread $thread): AttachmentCollection
    {
        $threads = new ThreadCollection(Thread::class, __FUNCTION__);
        return $this->fetchAttachmentsForThreads($threads);
    }

    protected function buildMessageIdsFromThreads(ThreadCollection $threads): array
    {
        $messageIds = [];
        /** @var Thread $thread */
        foreach ($threads as $thread) {
            array_merge($messageIds, $thread->getMessages()->getIds());
        }
        return array_unique(array_values($messageIds));
    }

    protected function buildAttachmentFilter(array $messageIds): AttachmentFilter
    {
        return (new AttachmentFilter())
            ->setLimit(static::FETCH_LIMIT)
            ->setPage(1)
            ->setMessageId($messageIds);
    }

    protected function fetchAttachments(AttachmentFilter $filter): AttachmentCollection
    {
        $attachments = new AttachmentCollection(Attachment::class, __FUNCTION__);
        $page = 0;

        do {
            $filter->setPage(++$page);
            try {
                $fetchedAttachments = $this->attachmentService->fetchCollectionByFilter($filter);
                $attachments->attachAll($fetchedAttachments);
            } catch (NotFound $exception) {
                return $attachments;
            }
        } while ($fetchedAttachments->getTotal() > static::FETCH_LIMIT * $page);

        return $attachments;
    }
}
