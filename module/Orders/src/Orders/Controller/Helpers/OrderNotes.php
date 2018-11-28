<?php
namespace Orders\Controller\Helpers;

use CG\Order\Shared\Note\Collection as OrderNoteCollection;
use CG\Order\Shared\Note\Entity as OrderNote;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\Entity as User;
use CG\User\Service as UserService;
use CG_UI\View\Helper\DateFormat as DateFormatHelper;

class OrderNotes
{
    const DELETED_USER = 'Deleted user';

    /** @var UserService $userService */
    protected $userService;
    /** @var DateFormatHelper $dateFormatHelper */
    protected $dateFormatHelper;

    public function __construct(UserService $userService, DateFormatHelper $dateFormatHelper)
    {
        $this->userService = $userService;
        $this->dateFormatHelper = $dateFormatHelper;
    }

    public function getNamesFromOrderNotes(OrderNoteCollection $notes)
    {
        $dateFormatter = $this->dateFormatHelper;
        $itemNotes = [];

        /** @var OrderNote $note */
        foreach ($notes as $note) {
            $itemNote = $note->toArray();
            $itemNote["eTag"] = $note->getStoredETag();
            $itemNote["timestamp"] = $dateFormatter($itemNote["timestamp"]);
            $itemNotes[] = $itemNote;
        }

        $userIds = [];
        foreach ($itemNotes as $itemNote) {
            $userIds[] = $itemNote["userId"];
        }

        if (empty($userIds)) {
            return $itemNotes;
        }

        $userIds = array_unique($userIds);
        try {
            $users = $this->userService->fetchCollection("all", null, null, null, $userIds);
            foreach ($itemNotes as &$note) {
                $note["author"] = $this->getNameForUser($users->getById($note["userId"]));
            }
        } catch (NotFound $exception) {
            // no users found for notes, don't return any authors
        }

        return $itemNotes;
    }

    protected function getNameForUser(?User $user)
    {
        if ($user == null) {
            return static::DELETED_USER;
        }
        return $user->getFirstName() . " " . $user->getLastName();
    }
}
