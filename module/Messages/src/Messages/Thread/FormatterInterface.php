<?php
namespace Messages\Thread;

use CG\Communication\Thread\Collection as ThreadCollection;

interface FormatterInterface
{
    /**
     * @return array that contains the overridden fields for each thread ID in the thread collection
     */
    public function __invoke(ThreadCollection $threads): array;
}