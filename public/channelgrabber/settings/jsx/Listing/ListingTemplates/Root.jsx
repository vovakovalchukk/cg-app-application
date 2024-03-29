import React, {useState} from 'react';
import Input from 'Common/Components/Input';
import FieldWithLabel from 'Common/Components/FieldWithLabel';

import AddTemplate from 'Common/Components/Templates/AddTemplate';
import TemplateSelect from 'Common/Components/Templates/TemplateSelect';
import TemplateEditor from 'Common/Components/Templates/TemplateEditor';

import {useFormInputState} from 'Common/Hooks/Form/input';
import {useTemplatesState} from 'Common/Hooks/Template/items';
import {useTemplateHtmlState} from 'Common/Hooks/Template/html';

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
            <div className="u-form-max-width-medium">
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
                        inputClassNames={'inputbox u-border-box'}
                    />
                </FieldWithLabel>
                }
            </div>


            {templateInitialised &&
                <TemplateEditor templateHTML={templateHTML} templateTags={props.listingTemplateTags}/>
            }

            {templateInitialised &&
                <div>
                    <button className={"u-margin-top-med button"} onClick={openPreview}>Preview</button>
                    <button className={"u-margin-top-med u-margin-left-small button"} onClick={save}>Save</button>
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
            data: {template: templateHTML.value}
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
            etag: templateSelectValue && templateSelectValue.etag,
            name: templateName.value
        };
        let response = await $.ajax({
            url: '/settings/listing/save',
            type: 'POST',
            dataType: 'json',
            data: params
        });

        if (response.success) {
            setTemplateSelectValue({
                id: response.success.id,
                etag: response.success.etag
            });
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