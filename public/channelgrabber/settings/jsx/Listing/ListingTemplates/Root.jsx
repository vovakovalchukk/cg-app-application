import React, {useState} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateEditor from 'ListingTemplates/Components/TemplateEditor';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';

const InitialFormSection = styled.section`
  max-width: 700px
`;

let previewWindow = null;

const RootComponent = props => {
    const {templates, setTemplates, deleteTemplateInState} = useTemplatesState(props.templates);
    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateHTML = useTemplateHtmlState('');

    return (
        <div className={"u-margin-top-xxlarge"}>
            <InitialFormSection>
                <TemplateSelect options={templates} selectedOption={templateSelectValue}
                                onOptionChange={(option) => {
                                    setTemplateSelectValue(option);
                                    setTemplateInitialised(true);
                                    templateName.setValue(option.name);
                                    templateHTML.setValue(option.template);
                                }}
                                deleteTemplate={deleteTemplateHandler}
                />

                <AddTemplate newTemplateName={newTemplateName} onAddClick={() => {
                    setTemplateInitialised(true);
                    templateName.setValue(newTemplateName.value);
                    templateHTML.setValue('');
                    setTemplateSelectValue({});
                }}
                />

                {templateInitialised &&
                <FieldWithLabel label={'Template Name'} className={'u-margin-top-small'}>
                    <Input
                        {...templateName}
                    />
                </FieldWithLabel>
                }
            </InitialFormSection>


            {templateInitialised &&
            <TemplateEditor templateHTML={templateHTML} listingTemplateTags={props.listingTemplateTags}/>
            }

            {templateInitialised &&
            <div>
                <button className={"u-margin-top-med button"} onClick={openPreview}>preview</button>
                <button className={"u-margin-top-med u-margin-left-small button"} onClick={save}>save</button>
            </div>
            }
        </div>
    );

    async function openPreview() {
        if (!templateHTML.value) {
            return;
        }
        let htmlToRender = null;

        let response = await $.ajax({
            url: '/settings/listing/preview',
            type: 'POST',
            dataType: 'json',
            data: {html: templateHTML.value}
        });

        if (response.success) {
            htmlToRender = response.success.data.html;
        }

        if (!htmlToRender) {
            return;
        }

        if (!previewWindow || previewWindow.closed) {
            previewWindow = window.open("", "previewWindow", "width=700,height=700");
        }
        previewWindow.document.open("text/html", "replace");
        previewWindow.document.write(htmlToRender);
        previewWindow.focus();
    }

    async function save() {
        const params = {
            template: templateHTML.value,
            id: templateSelectValue && templateSelectValue.id,
            name: templateName.value
        };
        let response = await $.ajax({
            url: '/settings/listing/save',
            type: 'POST',
            dataType: 'json',
            data: params
        });

        if (response.success) {
            n.success(response.success.message);
            return;
        }
        if (!response.error || !response.error.message) {
            return;
        }
        n.error(response.error.message);
    }

    async function deleteTemplateHandler() {
        if (!templateSelectValue) {
            return;
        }
        let response = await $.ajax({
            url: '/settings/listing/delete',
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

export default RootComponent;

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

function useTemplateHtmlState(initialValue) {
    const [value, setValue] = useState(initialValue);
    function onChange(e) {
        setValue(e.target.value);
    }

    function setTag(tag, position) {
        if (position === undefined || !tag) {
            return;
        }
        let newStr = `${value.slice(0, position)} {{${tag}}} ${value.slice(position)}`;
        setValue(newStr);
    }

    return {
        value,
        onChange,
        setValue,
        setTag
    }
}