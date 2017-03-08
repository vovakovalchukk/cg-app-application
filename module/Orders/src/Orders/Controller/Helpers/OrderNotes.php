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
                /** @var User $user */
                $user = $users->getById($note["userId"]);
                $note["author"] = $user->getFirstName() . " " . $user->getLastName();
            }
        } catch (NotFound $exception) {
            // no users found for notes, don't return any authors
        }

        return $itemNotes;
    }
}
