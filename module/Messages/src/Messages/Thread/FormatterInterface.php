<?php
namespace Messages\Thread;

use CG\Communication\Thread\Entity as Thread;

interface FormatterInterface
{
    /**
     * @return array $threadData with any amendments made to it
     */
    public function __invoke(array $threadData, Thread $thread): array;
}