import React, {useState, useEffect} from 'react';
import styled from 'styled-components';
import FormattingService from 'DataExchange/StockTemplates/Formatting/Service';

import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

//todo - move these to Common
import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';
import FieldMapper from 'DataExchange/StockTemplates/Components/FieldMapper';

import XHRService from 'DataExchange/StockTemplates/XHR/Service';

import Hooks from 'DataExchange/StockTemplates/Hooks/Hooks';
const {useTemplatesState, useTemplateState, useFormInputState, useCgOptionsState} = Hooks;

const containerWidth = 600;

const InitialFormSection = styled.section`
  padding-top: 1rem;
  max-width: ${containerWidth}px;
`;

const defaultColumn = {
    cgField: '',
    fileField: ''
};
const defaultTemplate = {
    id: null,
    name: '',
    type: 'stock',
    columnMap: [defaultColumn]
};

let initialCgOptions = null;

const App = props => {
    const formattedTemplates = formatTemplates(props.templates);
    let {templates, setTemplates, deleteTemplateInState} = useTemplatesState(formattedTemplates);

    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateState = useTemplateState(defaultTemplate);

    const formattedCgFieldOptions = formatCgFieldOptions(props.cgFieldOptions);

    const {cgFieldOptions, availableCgFieldOptions, setCgFieldOptions, updateCgOptionsFromSelections} = useCgOptionsState(formattedCgFieldOptions);

    if(!initialCgOptions) {
        initialCgOptions = cgFieldOptions;
    }

    return (
        <div>
            <InitialFormSection>
                <div className={'u-defloat'}>
                    <TemplateSelect options={templates}
                                    selectedOption={templateSelectValue}
                                    onOptionChange={(chosenTemplate) => {
                                        setTemplateSelectValue(chosenTemplate);
                                        setTemplateInitialised(true);
                                        templateName.setValue(chosenTemplate.name);
                                        let templateToSet = deepCopyObject(chosenTemplate);
                                        templateState.setTemplate(templateToSet);
                                    }}
                                    deleteTemplate={deleteTemplateHandler}
                    />

                    <AddTemplate newTemplateName={newTemplateName} onAddClick={() => {
                        setTemplateInitialised(true);
                        templateName.setValue(newTemplateName.value);
                        let templateToSet = deepCopyObject(defaultTemplate);
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
                        let template = templateState.deleteFieldRow(rowIndex, availableCgFieldOptions.length);
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
                        disabled={shouldDisableSave()}
//                        disabled={true}
                    >
                        Save
                    </button>
                </div>
                }
            </InitialFormSection>
        </div>
    );

    function shouldDisableSave() {
        debugger;
        let templateSelectValueForComparison = FormattingService.formatTemplateForSave(
            {...templateSelectValue},
            templateName.value
        );
        const formattedTemplate = FormattingService.formatTemplateForSave(
            templateState.template,
            templateName.value
        );
        return JSON.stringify(formattedTemplate, null, 1) ===
            JSON.stringify(templateSelectValueForComparison, null, 1);
    }

    async function saveTemplate() {
        const response = await XHRService.saveTemplate(templateState, templateName);

        if (!response.success) {
            return;
        }
        console.log('templates: ', templates);
        
        debugger;
//        const newTemplate = templateState.applyIdToTemplate(response.template.id);

        const newTemplate = response.template;

        const newSelectOption = {
            ...newTemplate,
            value: newTemplate.name
        };
        const newTemplates = formatTemplates([...templates, newSelectOption]);
        setTemplates(newTemplates);


        setTemplateSelectValue(newSelectOption);

//        templateState.setTemplate(
//            ...templateState.template,
//
//        );

        //todo - maybe need to set the template from the response
        
        
    }

    function changeField(rowIndex, desiredValue, propertyName) {
        let column = templateState.template.columnMap[rowIndex];
        let isPreviouslyBlankRow = !column.cgField && !column.fileField;

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

    async function deleteTemplateHandler() {


        //todo - test and implement this

        if (!templateSelectValue) {
            return;
        }
        let response = null;
        try {
            response = await $.ajax({
                url: '/dataExchange/stock/templates/remove',
                type: 'POST',
                dataType: 'json',
                data: {id: templateSelectValue.id}
            });

        } catch(error) {
            console.log('error',error);


        }
        if (response.success) {
            n.success(response.success.message);
            deleteTemplateInState(templateSelectValue);
            templateName.setValue('');
            templateHTML.setValue('');
            return;
        }

        if (!response.error || !response.error.message) {
            return;
        }
        n.error(response.error.message);
    }
};

export default App;

function formatCgFieldOptions(cgFieldOptions){
    let options = [];
    for (let key in cgFieldOptions) {
        options.push({
            title: key,
            name: key,

            value: cgFieldOptions[key]
        });
    }
    return options;
}

function addBlankRowToColumnMap(templateColumnMap) {
    templateColumnMap.push({...defaultColumn});
    return templateColumnMap;
}

function formatTemplates(templates, cgFieldOptionsLength) {
    return templates.map((template) => {
        if (template.columnMap.length === cgFieldOptionsLength || blankRowExistsAlreadyInTemplate(template)) {
            return template;
        }
        let newColumnMap = addBlankRowToColumnMap([...template.columnMap]);
        return {
            ...template,
            columnMap: newColumnMap
        };
    })
}

function blankRowExistsAlreadyInTemplate(template) {
    let columns = template.columnMap;
    if (!columns.length) {
        return false;
    }
    let lastColumn = columns[columns.length - 1];
    return isBlankColumn(lastColumn)
}

function isBlankColumn(column) {
    return !column.fileField && !column.cgField;
}

function deepCopyObject(object) {
    let newObject = JSON.stringify(object, null, 1);
    newObject = JSON.parse(newObject);
    return newObject;
}

function shouldAddNewRow(rowIndex, template, isBlankRow) {
    let isNotLastRow = rowIndex === template.columnMap.length - 1;
    let isNotAtMaxRows = template.columnMap.length !== initialCgOptions.length;
    return isNotLastRow && isBlankRow && isNotAtMaxRows;
}