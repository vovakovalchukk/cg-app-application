import React, {useState} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

// todo - move these over to cg-common
import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';
import FieldMapper from 'DataExchange/StockTemplates/Components/FieldMapper';


const InitialFormSection = styled.section`
  padding-top: 1rem;
  max-width: 700px
`;


//format to be sent to backend
//{
//    "id": integer,
//    "type": string,
//    "name": string,
//    "organisationUnitId": integer,
//    "columnMap": [
//    {
//        "id": integer,
//        "cgField": string,
//        "fileField": string
//    },
//    ...more objects like the previous one can go here
//]
//}


const App = props => {
    console.log('props: ', props);
    const {templates, setTemplates, deleteTemplateInState} = useTemplatesState(props.templates);
    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    //todo - change this to come from the templates.
    const defaultTemplate = {
        id: null,
        name: '',
        type: 'stock',
        columnMap: [{
            cgField: '',
            fileField: ''
        }]
    };

    let templateState = useTemplateState(defaultTemplate);
    // todo ^ this needs to read from available templates
    return (
        <div>
            <InitialFormSection>
                <div className={'u-defloat'}>
                    <TemplateSelect options={templates} selectedOption={templateSelectValue}
                                    onOptionChange={(option) => {
                                        setTemplateSelectValue(option);
                                        setTemplateInitialised(true);
                                        templateName.setValue(option.name);
                                        // todo - set field mapper component
                                        // templateHTML.setValue(option.template);

                                        debugger;
                                        templateState.setTemplate(option)
                                    }}
                                    deleteTemplate={deleteTemplateHandler}
                    />

                    <AddTemplate newTemplateName={newTemplateName} onAddClick={() => {
                        setTemplateInitialised(true);
                        templateName.setValue(newTemplateName.value);
                        //                    templateHTML.setValue('');
                        //todo - set field mapper component
                        setTemplateSelectValue({});
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

                <FieldMapper
                    template = {templateState.template}
                    addFieldRow = {templateState.addFieldRow}
                    //todo - extract method
                    removeFieldRow = {(rowIndex) => {
                        console.log('in removeFieldRow');
                        //todo something like the below
                        // selectOptions.updateOptions(/)
                        templateState.deleteFieldRow();
                    }}
                    //todo - extract method
                    changeFileField = {(rowIndex, desiredValue) => {
                        templateState.changeFileField(rowIndex, desiredValue)
                    }}
                    changeCgField = {(rowIndex, desiredValue) => {
                        templateState.changeCgField(rowIndex, desiredValue)
                    }}
                />
            </InitialFormSection>
        </div>
    );

    async function deleteTemplateHandler() {
        if (!templateSelectValue) {
            return;
        }
        let response = await $.ajax({
            url: '/dataExchange/stock/templates/remove',
            type: 'POST',
            dataType: 'json',
            data: {id: templateSelectValue.id}
        });

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

function useTemplateState(initialTemplate) {
//    let initialTemplateToUse = typeof initialTemplate === 'object' ? initialTemplate : {
//        id: null,
//        fields: []
//    };

    const [template, setTemplate] = useState(initialTemplate);

    const INPUT_FIELD = 'cgField';
    const SELECT_FIELD = 'fileField';

    const blankRow = {
        [INPUT_FIELD]: "",
        [SELECT_FIELD]: ""
    };

    let columnMap = template.columnMap;

    function addFieldRow() {
        let columnMap = columnMap.slice();
        columnMap.push(blankRow);
        setTemplate({
            ...template,
            columnMap
        });
    }

    function deleteFieldRow(index) {
        let columnMap = columnMap.slice();
        columnMap.splice(index, 1);
        setTemplate({
            ...template,
            columnMap
        });
    }

    function changeCgField(fieldIndex, desiredValue) {
        let columnMap = columnMap.slice();
        columnMap[fieldIndex][INPUT_FIELD] = desiredValue;
        setTemplate({
            ...template,
            columnMap
        });
    }

    function changeFileField(fieldIndex, desiredValue) {
        let columnMap = columnMap.slice();
        columnMap[fieldIndex][SELECT_FIELD] = desiredValue;
        setTemplate({
            ...template,
            columnMap
        })
    }

    return {
        template,
        setTemplate,
        addFieldRow,
        deleteFieldRow,
        changeCgField,
        changeFileField
    };
}

function useTemplatesState(initialTemplates) {
    initialTemplates = Array.isArray(initialTemplates) ? initialTemplates : [];
    const formattedTemplates = initialTemplates.map(template => {
        return {
            ...template,
            value: template.name
        };
    });
    const [templates, setTemplates] = useState(formattedTemplates);

    function deleteTemplateInState(template) {
        if (!template) {
            return;
        }
        let newTemplates = templates.slice();
        let templateIndex = newTemplates.findIndex(temp => temp === template);
        newTemplates.splice(templateIndex, 1);
        setTemplates(newTemplates);
    }

    return {
        templates,
        setTemplates,
        deleteTemplateInState
    };
}

function useFormInputState(initialValue) {
    const [value, setValue] = useState(initialValue);
    function onChange(e) {
        setValue(e.target.value);
    }
    return {
        value,
        onChange,
        setValue
    }
}