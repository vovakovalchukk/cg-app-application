import React, {useState} from 'react';
import styled from 'styled-components';

import Input from 'Common/Components/Input';
import FieldWrapper from 'Common/Components/FieldWrapper';

import AddTemplate from 'ListingTemplates/Components/AddTemplate';
import TemplateEditor from 'ListingTemplates/Components/TemplateEditor';
import TemplateSelect from 'ListingTemplates/Components/TemplateSelect';


const InitialFormSection = styled.section`
  max-width: 700px
`;

let previewWindow = null;

const RootComponent = props => {
    const formattedTemplates = props.templates.map(template => {
        return {
            ...template,
            value: template.name
        };
    });
    const templateName = useFormInput('');
    const newTemplateName = useFormInput('');

    const [templateInitialised, setTemplateInitialised] = useState();
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateHTML = useTemplateHtml('');

    return (
        <div className={"u-margin-top-xxlarge"}>
            <InitialFormSection>
                <TemplateSelect options={formattedTemplates} selectedOption={templateSelectValue}
                    onOptionChange={(option) => {
                        setTemplateSelectValue(option);
                        setTemplateInitialised(true);
                        templateName.setValue(option.name);
                        templateHTML.setValue(option.html);
                    }}
                    deleteTemplate={deleteTemplate}
                />

                <AddTemplate newTemplateName={newTemplateName} onAddClick={() => {
                    setTemplateInitialised(true);
                    templateName.setValue(newTemplateName.value);
                    templateHTML.setValue('');
                    setTemplateSelectValue({});
                }}
                />

                {templateInitialised &&
                <FieldWrapper label={'Template Name'} className={'u-margin-top-small'}>
                    <Input
                        {...templateName}
                    />
                </FieldWrapper>
                }
            </InitialFormSection>


            {templateInitialised &&
            <TemplateEditor templateHTML={templateHTML} listingTemplateTags={props.listingTemplateTags}/>
            }

            {templateInitialised &&
            <div>
                <button className={"u-margin-top-med"} onClick={openPreview}>preview</button>
                <button className={"u-margin-top-med u-margin-left-small"} onClick={save}>save</button>
            </div>
            }
        </div>
    );

    async function openPreview(){
        if(!templateHTML.value){
            return;
        }
        let htmlToRender = null;
        await $.ajax({
            url: '/settings/listing/preview',
            type: 'POST',
            dataType: 'json',
            data: {html: templateHTML.value}
        }).then((response) => {
            if(response.success){
                htmlToRender = response.success.data.html;
            }
        });

        if(!htmlToRender){
            return;
        }

        if(!previewWindow || previewWindow.closed){
            previewWindow = window.open("", "previewWindow", "width=700,height=700");
        }
        previewWindow.document.open("text/html", "replace");
        previewWindow.document.write(htmlToRender);
        previewWindow.focus();
    }

    async function save(){
        const params = {
            html: templateHTML.value,
            id: templateSelectValue && templateSelectValue.id,
            name: templateName.value
        };
        await $.ajax({
            url: '/settings/listing/save',
            type: 'POST',
            dataType: 'json',
            data: params
        }).then((response) => {
            if(response.success){
                n.success(response.success.message);
                return;
            }
            if(!response.error || response.error.message){
                return;
            }
            n.error(response.error.message);
        });
    }
    
    async function deleteTemplate(){
        console.log('in delete template');
        
        
    }
};

export default RootComponent;

function useFormInput(initialValue) {
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

function useTemplateHtml(initialValue){
    const [value, setValue] = useState(initialValue);
    function onChange(e){
        setValue(e.target.value);
    }

    function setTag(tag, position){
        if(!position || !tag){
            return;
        }
        let newStr = `${value.slice(0, position)} {{ ${tag} }} ${value.slice(position)}`;
        setValue(newStr);
    }

    return {
        value,
        onChange,
        setValue,
        setTag
    }
}