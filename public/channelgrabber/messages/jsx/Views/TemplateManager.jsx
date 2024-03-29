import React, {useState} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';

import FieldWithLabel from 'Common/Components/FieldWithLabel';
import AddTemplate from 'Common/Components/Templates/AddTemplate';
import TemplateSelect from 'Common/Components/Templates/TemplateSelect';
import TemplateEditor from 'Common/Components/Templates/TemplateEditor';

import {useFormInputState} from 'Common/Hooks/Form/input';
import {useTemplatesState} from 'Common/Hooks/Template/items';
import {useTemplateHtmlState} from 'Common/Hooks/Template/html';

import ButtonSelect from 'Common/Components/ButtonSelect';

let previewWindow = null;

const StyledButtonSelect = styled(ButtonSelect)`
    width: auto;
    min-width: 14rem;
    
    .bulkaction-dropdown {
        left: 0px;
        right: auto;
    }
`;

const StyledTemplateEditor = styled.div`
    fieldset {
        margin: 1rem 0 0;
    }
    
    textarea {
        resize: vertical;
        box-sizing: border-box;
    }
`;

const MessagesGridTemplates = styled.div`
    grid-row: main/foot;
    grid-column: main/right;
    overflow: auto;
    padding: 20px;
`;

const TemplateManager = (props) => {
    const {match, accounts} = props;
    const {params} = match;

    const {templates, setTemplates, deleteTemplateInState} = useTemplatesState(getFormattedTemplates(props.templates));
    const templateName = useFormInputState('');
    const newTemplateName = useFormInputState('');

    const [templateInitialised, setTemplateInitialised] = useState(false);
    const [templateSelectValue, setTemplateSelectValue] = useState(null);
    const templateHTML = useTemplateHtmlState('');

    const [previewAccountValue, setPreviewAccountValue] = useState(accounts[0].value);

    return (
        <MessagesGridTemplates>
            <div className="u-form-max-width-medium">
                <span className="heading-large u-defloat">
                    Message Templates
                </span>
                <TemplateSelect
                    options={templates}
                    selectedOption={templateSelectValue}
                    onOptionChange={(chosenTemplate) => {
                        setTemplateSelectValue(chosenTemplate);
                        setTemplateInitialised(true);
                        templateName.setValue(chosenTemplate.name);
                        templateHTML.setValue(chosenTemplate.template);
                    }}
                    deleteTemplate={deleteTemplateHandler}
                />

                <AddTemplate
                    newTemplateName={newTemplateName}
                    onAddClick={() => {
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

                {templateInitialised &&
                    <StyledTemplateEditor>
                        <TemplateEditor templateHTML={templateHTML} templateTags={props.messageTemplateTags}/>
                    </StyledTemplateEditor>
                }

                {templateInitialised &&
                    <div className={"u-margin-top-med"}>
                        <StyledButtonSelect
                            options={formatAccounts(accounts)}
                            ButtonTitle={() => (
                                <span>Preview for <b>{fetchAccountTextForPreviewButton()}</b></span>
                            )}
                            multiSelect={false}
                            onButtonClick={openPreview}
                            onSelect={(ids) => {
                                setPreviewAccountValue(ids[0]);
                            }}
                        />
                        <button className={"u-margin-left-small button"} onClick={save}>Save</button>
                    </div>
                }
            </div>
        </MessagesGridTemplates>
    );

    async function openPreview() {
        if (!templateHTML.value) {
            return;
        }
        let htmlToRender = null;

        let response = await $.ajax({
            url: '/messages/templates/preview',
            type: 'POST',
            dataType: 'json',
            data: {
                template: templateHTML.value,
                accountId: previewAccountValue
            }
        });

        if (response.success) {
            htmlToRender = response.content.nl2br();
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
        let response = await $.ajax({
            url: '/messages/templates/save',
            type: 'POST',
            dataType: 'json',
            data: {
                template: templateHTML.value,
                id: templateSelectValue && templateSelectValue.id,
                etag: templateSelectValue && templateSelectValue.etag,
                name: templateName.value,
            }
        });

        if (response.success) {
            setTemplateSelectValue({
                id: response.id,
                etag: response.etag
            });
            n.success("You have successfully saved your message template.");

            const newTemplate = {
                id: response.id,
                name: templateName.value,
                template: templateHTML.value
            };

            props.actions.addTemplate(newTemplate);

            const hookTemplates = [...templates];

            const templateIndex = templates.findIndex(template => {
                return template.id === newTemplate.id;
            });

            const targetIndex = templateIndex > -1 ? templateIndex : hookTemplates.length;

            hookTemplates[targetIndex] = newTemplate;

            setTemplates(hookTemplates);

            return;
        }
        if (!response.error || !response.error.message) {
            return;
        }
        n.error("We were unable to save your message template. Please contact support for assistance.");
    }

    async function deleteTemplateHandler() {
        if (!templateSelectValue) {
            return;
        }
        let response = await $.ajax({
            url: '/messages/templates/delete',
            type: 'POST',
            dataType: 'json',
            data: {
                id: templateSelectValue.id
            }
        });

        if (response.success) {
            const popupText = typeof response.success.message === 'undefined' ? 'Template deleted' : response.success.message;
            n.success(popupText);
            deleteTemplateInState(templateSelectValue);
            templateName.setValue('');
            templateHTML.setValue('');
            setTemplateSelectValue({});
            props.actions.removeTemplate(templateSelectValue.id);
            return;
        }

        if (!response.error || !response.error.message) {
            return;
        }
        n.error(response.error.message);
    }

    function fetchAccountTextForPreviewButton() {
        const stringLengthLimit = 30;
        let textToRender = fetchByValue(accounts, previewAccountValue).name;
        if (textToRender.length > stringLengthLimit) {
            textToRender = textToRender.substring(0, stringLengthLimit) + '...';
        }
        return textToRender;
    }
};

export default TemplateManager;

function fetchByValue(options, value) {
    return options.find((option) => {
        return option.value === value
    });
}

function formatAccounts(options) {
    return options.map((option) => {
        return {
            ...option,
            id: option.value
        };
    });
}

function getFormattedTemplates(templates) {
    return Object.keys(templates.byId).map((id) => {
        return templates.byId[id];
    });
}