<?php
namespace SetupWizard\Company;

use CG\Stdlib\Exception\Runtime\MessagesException;
use CG_Register\Company\FormValidationInterface;
use CG_UI\Form\Fieldset\Address;
use Zend\Form\Form;

class FormValidation implements FormValidationInterface
{
    protected $requiredFields = [
        'addressFullName',
        'addressLine1',
        'city',
        'postcode',
        'country',
        'phoneNumber',
        'emailAddress',
    ];

    public function validateCompanyFromDetailsForm(Form $form, MessagesException $e): void
    {
        /** @var Address $address */
        $address = $form->get('address');
        foreach ($this->requiredFields as $requiredField) {
            $field = $address->get($requiredField);
            if (strlen(trim($field->getValue())) > 0) {
                continue;
            }
            $e->addError(sprintf('%s is a required field', $field->getLabel()));
        }
    }
}