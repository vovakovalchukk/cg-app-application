<?php
namespace Settings\Invoice;

use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Helper
{
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrganisationUnitService $ouService */
    protected $ouService;

    public function __construct(ActiveUserInterface $activeUserContainer, OrganisationUnitService $ouService)
    {
        $this->activeUserContainer = $activeUserContainer;
        $this->ouService = $ouService;
    }

    /**
     * @return OrganisationUnit[]
     */
    public function getTradingCompanies()
    {
        try {
            return iterator_to_array(
                $this->ouService->fetchFiltered(
                    'all',
                    1,
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
                )
            );
        } catch (NotFound $exception) {
            return [];
        }
    }
}
