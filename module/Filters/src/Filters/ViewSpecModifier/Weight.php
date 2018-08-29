<?php
namespace Filters\ViewSpecModifier;

use CG\Locale\Mass;
use CG\User\ActiveUserInterface;
use CG_UI\View\Filters\ViewSpecModifierInterface;

class Weight implements ViewSpecModifierInterface
{
    /** @var ActiveUserInterface */
    protected $activeUser;

    public function __construct(ActiveUserInterface $activeUser)
    {
        $this->activeUser = $activeUser;
    }

    public function modifySpec(array &$spec): void
    {
        if (isset($spec['variables']['title'])) {
            $spec['variables']['title'] = sprintf(
                $spec['variables']['title'],
                ucfirst(Mass::getForLocale($this->activeUser->getLocale()))
            );
        }
    }
}