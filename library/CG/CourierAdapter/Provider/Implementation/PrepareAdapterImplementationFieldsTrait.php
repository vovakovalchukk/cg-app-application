<?php
namespace CG\CourierAdapter\Provider\Implementation;

use InvalidArgumentException;
use Zend\Form\Element as ZendFormElement;
use Zend\Form\Fieldset as ZendFormFieldset;
use Zend\Form\Form as ZendForm;

trait PrepareAdapterImplementationFieldsTrait
{
    protected function convertAdapterImplementationFieldsToForm(array $fields, array $values = [])
    {
        $form = new ZendForm();

        $this->prepareAdapterImplementationFields($fields);
        foreach ($fields as $field) {
            $form->add($field);
        }

        if ($values) {
            $form->setData($values);
        }
        return $form;
    }

    protected function prepareAdapterImplementationFields(array $fields)
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field instanceof ZendFormFieldset) {
                $this->prepareAdapterImplementationFields($field->getElements());
                continue;
            }
            if ($field->getOption('required')) {
                $class = $field->getAttribute('class') ?: '';
                $field->setAttribute('class', $class . ' required');
            }
        }
    }
}