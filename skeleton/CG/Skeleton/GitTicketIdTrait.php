<?php
namespace CG\Skeleton;

define('TICKET_REGEX','/([A-Z]+-[0-9]+)/');

trait GitTicketIdTrait
{
    protected function getGitTicketId()
    {
        $branch = shell_exec('git rev-parse --abbrev-ref HEAD');
        if(!preg_match(TICKET_REGEX, $branch, $matches)) {
            return;
        }
        return $matches[0];
    }
}