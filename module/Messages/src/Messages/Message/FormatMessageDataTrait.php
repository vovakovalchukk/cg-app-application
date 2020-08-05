<?php
namespace Messages\Message;

use CG\Communication\Message\Attachment\Collection as Attachments;
use CG\Communication\Message\Attachment\Entity as Attachment;
use CG\Communication\Message\Entity as Message;
use CG\Communication\Thread\Entity as Thread;
use CG\Stdlib\DateTime as StdlibDateTime;

trait FormatMessageDataTrait
{
    protected function formatMessageData(Message $message, Thread $thread, Attachments $attachments = null)
    {
        $messageData = $message->toArray();
        $dateFormatter = $this->getDateFormatter();
        $messageData['createdFuzzy'] = (new StdlibDateTime($messageData['created']))->fuzzyFormat();
        $messageData['created'] = $dateFormatter($messageData['created']);
        $messageData['personType'] = ($message->getExternalUsername() == $thread->getExternalUsername() ? 'customer' : 'staff');
        $messageData['attachments'] = $attachments instanceof Attachments ? $this->formatAttachments($attachments) : [];
        return $messageData;
    }

    private function formatAttachments(Attachments $attachments): array
    {
        $data = [];
        /** @var Attachment $attachment */
        foreach ($attachments as $attachment) {
            $data[] = [
                'name' => $attachment->getName(),
                'url' => $attachment->getUrl()
            ];
        }
        return $data;
    }

    abstract protected function getDateFormatter();
}