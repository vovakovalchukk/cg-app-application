<?php
namespace Messages\Message;

use CG\Communication\Message\Entity as Message;
use CG\Communication\Thread\Entity as Thread;
use CG\Stdlib\DateTime as StdlibDateTime;

trait FormatMessageDataTrait
{
    protected function formatMessageData(Message $message, Thread $thread)
    {
        $messageData = $message->toArray();
        $dateFormatter = $this->getDateFormatter();
        $messageData['created'] = $dateFormatter($messageData['created'], StdlibDateTime::FORMAT);
        $created = new StdlibDateTime($messageData['created']);
        $messageData['created'] = $created->uiFormat();
        $messageData['createdFuzzy'] = $created->fuzzyFormat();
        $messageData['personType'] = ($message->getExternalUsername() == $thread->getExternalUsername() ? 'customer' : 'staff');
        return $messageData;
    }

    abstract protected function getDateFormatter();
}