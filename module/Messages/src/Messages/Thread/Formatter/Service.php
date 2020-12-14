<?php
namespace Messages\Thread\Formatter;

use CG\Communication\Thread\Collection as ThreadCollection;

class Service
{
    /** @var Factory */
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function formatThreadsDataOverrides(ThreadCollection $threads): array
    {
        $overridesForChannel = [];

        $channels = array_unique($threads->getArrayOf('channel'));
        foreach ($channels as $channel) {
            $formatterForChannel = ($this->factory)($threads, $channel);
            if (!$formatterForChannel) {
                continue;
            }
            $overridesForChannel[$channel] = $formatterForChannel($threads);
        }

        return $overridesForChannel;
    }
}
