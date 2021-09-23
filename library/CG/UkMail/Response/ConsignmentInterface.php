<?php
namespace CG\UkMail\Response;

use CG\UkMail\Consignment\Identifier;
use CG\UkMail\Consignment\Label;

interface ConsignmentInterface
{
    /**
     * @return Identifier[]
     */
    public function getIdentifiers(): array;

    /**
     * @return Label[]
     */
    public function getLabels(): array;
}