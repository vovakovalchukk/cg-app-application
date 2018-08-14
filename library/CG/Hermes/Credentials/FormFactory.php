<?php
namespace CG\Hermes\Credentials;

use CG\CourierAdapter\AddressInterface;
use Zend\Form\Element;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Email;
use Zend\Form\Element\Number;
use Zend\Form\Element\Password;
use Zend\Form\Element\Time;
use Zend\Form\Fieldset;
use Zend\Form\Form;

class FormFactory
{
    public function getCredentialsForm(): Form
    {
        $form = new Form();
        $form->add((new Element("clientName", [
            "label" => "Client Name"
        ]))->setAttribute('required', true));
        $form->add((new Element("clientId", [
            "label" => "Client ID"
        ]))->setAttribute('required', true));
        $form->add((new Element("username", [
            "label" => "Username"
        ]))->setAttribute('required', true));
        $form->add((new Password("password", [
            "label" => "Password"
        ]))->setAttribute('required', true));
        $form->add(new Checkbox("liveCredentials", [
            "label" => "Are these live credentials?",
            "checked_value" => 1,
            'unchecked_value' => 0,
            'label_attributes' => [
                'title' => 'Hermes will first give you test credentials then, after approval, live credentials. Please specify which these are.'
            ]
        ]));
        $form->add(new Checkbox("testPackApproved", [
            "label" => "Has your live test pack been approved by Hermes?",
            "checked_value" => 1,
            'unchecked_value' => 0,
            'label_attributes' => [
                'title' => 'Only check this after your test pack generated with your LIVE credentials has been approved by Hermes. This fully activates your Hermes account.'
            ]
        ]));
        return $form;
    }

    public function getCredentialsRequestForm(AddressInterface $address, string $companyName): Form
    {
        $form = new Form();
        $form->add((new Element('Company Registration Number', [
            'label' => 'Company Registration Number'
        ]))->setAttribute('required', true));
        $form->add((new Element('Company Name', [
            'label' => 'Company Name',
        ]))->setAttribute('required', true)->setValue($companyName));
        $form->add((new Element('Contact Name', [
            'label' => 'Contact Name'
        ]))->setAttribute('required', true)->setValue($address->getFirstName() . ' ' . $address->getLastName()));
        $form->add((new Email('Contact Email', [
            'label' => 'Contact Email'
        ]))->setAttribute('required', true)->setValue($address->getEmailAddress()));
        $companyAddress = $this->getAddressFieldset('Company Address', $address);
        $form->add($companyAddress);
        $collectionAddress = $this->getAddressFieldset('Collection Address (if different)', null, false);
        $form->add($collectionAddress);
        $form->add((new Time('Preferred Collection Time Slot', [
            'label' => 'Preferred Collection Time Slot',
            'format' => 'H:i'
        ]))->setAttribute('required', true));
        $form->add((new Element('Average Weekly Volumes', [
            'label' => 'Average Weekly Volumes (via Hermes)'
        ]))->setAttribute('required', true));
        $form->add($this->getVolumeSplitPerWeightFieldset());
        $form->add($this->getVolumeSplitPerServiceFieldset());
        $form->add($this->getAverageParcelDimensionsFieldset());
        $form->add((new Element('Parcel Presentation', [
            'label' => 'Parcel presentation (i.e. Pallets / Cages / Sacks)'
        ]))->setAttribute('required', true));
        $form->add((new Element('Parcel Contents', [
            'label' => 'Contents of the parcels you are wishing to send'
        ]))->setAttribute('required', true));

        return $form;
    }

    protected function getAddressFieldset(
        string $name,
        ?AddressInterface $address = null,
        ?bool $required = true
    ): Fieldset  {
        $addressFields = new Fieldset($name, [
            'label' => $name
        ]);
        $addressFields->add((new Element('Line 1', [
            'label' => 'Line 1'
        ]))->setAttribute('required', $required)->setValue($address ? $address->getLine1() : null));
        $addressFields->add((new Element('Line 2', [
            'label' => 'Line 2'
        ]))->setValue($address ? $address->getLine2() : null));
        $addressFields->add((new Element('Line 3', [
            'label' => 'Line 3'
        ]))->setValue($address ? $address->getLine3() : null));
        $addressFields->add((new Element('City', [
            'label' => 'City'
        ]))->setAttribute('required', $required)->setValue($address ? $address->getLine4() : null));
        $addressFields->add(new Element('County', [
            'label' => 'County'
        ]));
        $addressFields->add((new Element('Post code', [
            'label' => 'Post code'
        ]))->setAttribute('required', $required)->setValue($address ? $address->getPostCode() : null));
        return $addressFields;
    }

    protected function getVolumeSplitPerWeightFieldset(): Fieldset
    {
        $weightSplit = new Fieldset('Percentage Volume Split per Weight Band', [
            'label' => '% Volume Split per Weight Band'
        ]);
        $weightSplit->add((new Number('0-1kg', [
            'label' => '0 - 1kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Number('1-2kg', [
            'label' => '1 - 2kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Number('2-5kg', [
            'label' => '2 - 5kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Number('5-10kg', [
            'label' => '5 - 10kg'
        ]))->setAttribute('required', true));
        $weightSplit->add((new Number('10-15kg', [
            'label' => '10 - 15kg'
        ]))->setAttribute('required', true));
        return $weightSplit;
    }

    protected function getVolumeSplitPerServiceFieldset(): Fieldset
    {
        $serviceSplit = new Fieldset('Percentage Volume Split per Service', [
            'label' => '% Volume Split per Service'
        ]);
        $serviceSplit->add((new Number('UK48', [
            'label' => 'UK 48'
        ]))->setAttribute('required', true));
        $serviceSplit->add((new Number('Next Day', [
            'label' => 'Next Day'
        ]))->setAttribute('required', true));
        $serviceSplit->add((new Number('International', [
            'label' => 'International'
        ]))->setAttribute('required', true));
        return $serviceSplit;
    }

    protected function getAverageParcelDimensionsFieldset(): Fieldset
    {
        $dimensions = new Fieldset('Average Parcel Dimensions (cm)', [
            'label' => 'Average Parcel Dimensions (cm)'
        ]);
        $dimensions->add((new Number('Width', [
            'label' => 'Width'
        ]))->setAttribute('required', true));
        $dimensions->add((new Number('Length', [
            'label' => 'Length'
        ]))->setAttribute('required', true));
        $dimensions->add((new Number('Height', [
            'label' => 'Height'
        ]))->setAttribute('required', true));
        return $dimensions;
    }
}