<?php
namespace CG\ShipStation;

use function CG\Stdlib\hyphenToClassname;

trait GetClassNameForChannelTrait
{
    protected function getClassNameForChannel(string $channel)
    {
        return hyphenToClassname(preg_replace('/-ss$/', '', $channel));
    }
}