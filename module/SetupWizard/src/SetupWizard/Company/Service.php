<?php
namespace SetupWizard\Company;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\Conflict;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service
implements LoggerAwareInterface
{
use LogTrait;
    const MAX_SAVE_ATTEMPTS = 2;

    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(OrganisationUnitService $organisationUnitService)
    {
        $this->setOrganisationUnitService($organisationUnitService);
    }

    public function saveCompanyDetails($rootOuId, array $details, $attempt = 1)
    {
        $ou = $this->organisationUnitService->fetch($rootOuId);
        foreach ($details as $field => $value) {
            if ($field == 'address' && is_array($value)) {
                $contact = $ou->getContact();
                foreach ($value as $addressField => $addressValue) {
                    $this->setEntityValue($contact, $addressField, $addressValue);
                }
                continue;
            }
            if ($field == 'vatRegistered' && $value == 'on') {
                $value = true;
            }
            $this->setEntityValue($ou, $field, $value);
        }
        if (!isset($details['vatRegistered'])) {
            $ou->setVatRegistered(false);
        }
        try {
            $this->organisationUnitService->save($ou);
        } catch (NotModified $e) {
            // No-op
        } catch (Conflict $e) {
            if ($attempt >= static::MAX_SAVE_ATTEMPTS) {
                throw $e;
            }
            $this->saveCompanyDetails($rootOuId, $details, ++$attempt);
        }
    }

    protected function setEntityValue($entity, $field, $value)
    {
        $setter = 'set' . ucfirst($field);
        if (!is_callable([$entity, $setter])) {
            return;
        }
        $entity->$setter($value);
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}
