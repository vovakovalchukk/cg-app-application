import React, {useState} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

// todo - move these over to cg-common
import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';
import FieldMapper from 'DataExchange/StockTemplates/Components/FieldMapper';


const InitialFormSection = styled.section`
  max-width: 700px
`;
const App = props => {
    console.log('props: ', props);
    const {templates, setTemplates, deleteTemplateInState} = useTemplatesState(props.templates);
    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    return (
        <InitialFormSection>
            <TemplateSelect options={templates} selectedOption={templateSelectValue}
                            onOptionChange={(option) => {
                                setTemplateSelectValue(option);
                                setTemplateInitialised(true);
                                templateName.setValue(option.name);

                                //todo - set field mapper component
//                                templateHTML.setValue(option.template);
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

            <FieldMapper


            />
        </InitialFormSection>
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