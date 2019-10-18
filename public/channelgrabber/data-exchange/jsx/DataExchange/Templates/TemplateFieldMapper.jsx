import React, {useState} from 'react';
import styled from 'styled-components';
import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';
import AddTemplate from 'Common/Components/Templates/AddTemplate';
import TemplateSelect from 'Common/Components/Templates/TemplateSelect';
import FieldMapper from 'DataExchange/Templates/Components/FieldMapper';
import XHRService from 'DataExchange/Templates/XHR/Service';
import FormattingService from 'DataExchange/Templates/Formatting/Service';
import Hooks from 'DataExchange/Templates/Hooks/Hooks';

const {useTemplatesState, useTemplateState, useFormInputState, useCgOptionsState} = Hooks;

const containerWidth = 600;

const InitialFormSection = styled.section`
  padding-top: 1rem;
  max-width: ${containerWidth}px;
`;

let initialCgOptions = null;

const TemplateFieldMapper = (props) => {
    const formattedTemplates = FormattingService.formatTemplates(props.templates);
    let {templates, setTemplates} = useTemplatesState(formattedTemplates);

    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateState = useTemplateState(FormattingService.getDefaultTemplate(props.templateType));

    const formattedCgFieldOptions = FormattingService.formatCgFieldOptions(props.cgFieldOptions);

    const {cgFieldOptions, availableCgFieldOptions, setCgFieldOptions, updateCgOptionsFromSelections} = useCgOptionsState(formattedCgFieldOptions);

    if (!initialCgOptions) {
        initialCgOptions = cgFieldOptions;
    }

    return (
        <div>
            <InitialFormSection>
                <div className={'u-defloat'}>
                    <TemplateSelect
                        options={templates}
                        selectedOption={templateSelectValue}
                        onOptionChange={(chosenTemplate) => {
                            setTemplateSelectValue(chosenTemplate);
                            setTemplateInitialised(true);
                            templateName.setValue(chosenTemplate.name);
                            let templateToSet = deepCopyObject(chosenTemplate);
                            templateState.setTemplate(templateToSet);
                            updateCgOptionsFromSelections(templateState.template, initialCgOptions)

                        }}
                        deleteTemplate={deleteTemplateHandler}
                    />

                    <AddTemplate
                        newTemplateName={newTemplateName}
                        onAddClick={() => {
                            setTemplateInitialised(true);
                            templateName.setValue(newTemplateName.value);
                            let templateToSet = deepCopyObject(FormattingService.getDefaultTemplate(props.templateType));
                            templateState.setTemplate(templateToSet)
                        }}
                    />

                    {templateInitialised &&
                        <FieldWithLabel label={'Template Name'} className={'u-margin-top-small'}>
                            <Input
                                {...templateName}
                                inputClassNames={'inputbox u-border-box'}
                            />
                        </FieldWithLabel>
                    }
                </div>

                {templateInitialised &&
                <FieldMapper
                    template = {templateState.template}
                    addFieldRow = {templateState.addFieldRow}
                    availableCgFieldOptions={availableCgFieldOptions}
                    allCgFieldOptions={cgFieldOptions}
                    removeFieldRow = {(rowIndex) => {
                        const template = templateState.deleteFieldRow(rowIndex, availableCgFieldOptions.length);
                        updateCgOptionsFromSelections(template, initialCgOptions);
                    }}
                    changeFileField = {(rowIndex, desiredValue) => {
                        changeField(rowIndex, desiredValue, 'fileField');
                        updateCgOptionsFromSelections(templateState.template, initialCgOptions)
                    }}
                    changeCgField = {(rowIndex, desiredValue) => {
                        changeField(rowIndex, desiredValue, 'cgField');
                        updateCgOptionsFromSelections(templateState.template, initialCgOptions)
                    }}
                    containerWidth={containerWidth}
                />}

                {templateInitialised &&
                <div>
                    <button
                        className={"u-margin-top-med button"}
                        onClick={saveTemplate}
                        disabled={isSaveDisabled()}
                    >
                        Save
                    </button>
                </div>
                }
            </InitialFormSection>
        </div>
    );

    function isSaveDisabled() {
        if (typeof templateSelectValue !== 'object' || !Object.keys(templateSelectValue).length) {
            return false;
        }

        const templateSelectValueForComparison = FormattingService.formatTemplateForSave(
            {...templateSelectValue}
        );
        const formattedTemplate = FormattingService.formatTemplateForSave(
            templateState.template,
            templateName.value
        );
        return JSON.stringify(formattedTemplate, null, 1) ===
            JSON.stringify(templateSelectValueForComparison, null, 1);
    }

    async function saveTemplate() {
        const response = await XHRService.saveTemplate(templateState, templateName, props.xhrRoute);
        if (!response.success) {
            return;
        }
        applySelectOptionChangesAfterSave(response);
    }

    async function deleteTemplateHandler() {
        const response = await XHRService.deleteTemplate(templateSelectValue, props.xhrRoute);
        if (!response.success) {
            return;
        }
        const newTemplates = templates.filter((template) => template.id !== response.templateId);
        setTemplates(newTemplates);
        setTemplateSelectValue({});
        setTemplateInitialised(false);
    }

    function applySelectOptionChangesAfterSave(response) {
        const newTemplate = response.template;
        const newSelectOption = {
            ...newTemplate,
            value: newTemplate.name
        };
        const newTemplates = FormattingService.formatTemplates([...templates, newSelectOption]);
        setTemplates(newTemplates);
        setTemplateSelectValue(newSelectOption);
    }

    function changeField(rowIndex, desiredValue, propertyName) {
        const column = templateState.template.columnMap[rowIndex];
        const isPreviouslyBlankRow = !column.cgField && !column.fileField;

        if (propertyName === 'cgField') {
            templateState.changeCgField(rowIndex, desiredValue);
        } else {
            templateState.changeFileField(rowIndex, desiredValue);
        }

        if (!shouldAddNewRow(rowIndex, templateState.template, isPreviouslyBlankRow)) {
            return;
        }

        templateState.addFieldRow();
    }
};

export default TemplateFieldMapper;

function deepCopyObject(object) {
    return JSON.parse(JSON.stringify(object, null, 1));
}

function shouldAddNewRow(rowIndex, template, isBlankRow) {
    let isNotLastRow = rowIndex === template.columnMap.length - 1;
    let isNotAtMaxRows = template.columnMap.length !== initialCgOptions.length;
    return isNotLastRow && isBlankRow && isNotAtMaxRows;
}