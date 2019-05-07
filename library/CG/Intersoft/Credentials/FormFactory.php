<?php
namespace CG\Intersoft\Credentials;

use Zend\Form\Element;
use Zend\Form\Element\Password;
use Zend\Form\Form;

class FormFactory
{
    public function __invoke()
    {
        $form = new Form();
        $form->add((new Element("applicationId", [
            "label" => "Application ID"
        ]))->setAttribute('required', true));
        $form->add((new Element("postingLocationNumber", [
            "label" => "Posting Location Number"
        ]))->setAttribute('required', true));
        $form->add((new Element("userId", [
            "label" => "User ID"
        ]))->setAttribute('required', true));
        $form->add((new Password("password", [
            "label" => "Password"
        ]))->setAttribute('required', true));
        return $form;
    }

    public function getFirstTimeForm()
    {
        $form = new Form();
        $form->add((new Element("companyName", [
            "label" => "Company Name"
        ]))->setAttribute('required', true));
        $form->add((new Element("addressLine1", [
            "label" => "Address Line 1"
        ]))->setAttribute('required', true));
        $form->add((new Element("addressLine2", [
            "label" => "Address Line 2"
        ]))->setAttribute('required', true));
        $form->add((new Element("addressLine3", [
            "label" => "Address Line 3"
        ]))->setAttribute('required', false));
        $form->add((new Element("town", [
            "label" => "Town"
        ]))->setAttribute('required', true));
        $form->add((new Element("county", [
            "label" => "County"
        ]))->setAttribute('required', true));
        $form->add((new Element("postcode", [
            "label" => "Postcode"
        ]))->setAttribute('required', true));
        $form->add((new Element("contactName", [
            "label" => "Contact Name"
        ]))->setAttribute('required', true));
        $form->add((new Element("phoneNumber", [
            "label" => "Phone Number"
        ]))->setAttribute('required', true));
        $form->add((new Element("emailAddress", [
            "label" => "Email Address"
        ]))->setAttribute('required', true));
        $form->add((new Element("royalMailAccountNumber", [
            "label" => "Royal Mail Account Number"
        ]))->setAttribute('required', true));
        $form->add((new Element("royalMailPostingLocation", [
            "label" => "Royal Mail Posting Location"
        ]))->setAttribute('required', true));
        $form->add((new Element("royalMailObaEmailAddress", [
            "label" => "Royal Mail OBA Email Address"
        ]))->setAttribute('required', true));
        return $form;
    }
}