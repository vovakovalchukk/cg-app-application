import React, {useState} from 'react';
import Select from 'Common/Components/Select';
import Input from 'Common/Components/Input';
import FieldWrapper from 'Common/Components/FieldWrapper';

import TemplateEditor from 'ListingTemplates/Components/TemplateEditor';

const RootComponent = props => {
    const templateName = useFormInput('');
    const newTemplateName = useFormInput('');

    const [templateInitialised, setTemplateInitialised] = useState();
    const [templateSelectValue, setTemplateSelectValue] = useState({});

    const templateHTML = useTemplateHtml('');

    const options = [
        {name: 'option1', label: 'option1', value: 'option1', template: '<div>this is option1</div>'},
        {name: 'option2', label: 'option2', value: 'option2', template: 'this is option 2'},
        {name: 'option3', label: 'option3', value: 'option3', template: '<h2> this is option 3 </h2>'},
    ];

    return (
        <div className={"u-margin-top-med"}>
            <FieldWrapper label={'Load Template'} className={'u-margin-top-small'}>
                <Select
                    options={options}
                    autoSelectFirst={false}
                    title={'choose your template to load'}
                    selectedOption={templateSelectValue}
                    onOptionChange={(option)=>{
                        setTemplateSelectValue(option);
                        setTemplateInitialised(true);
                        templateName.setValue(option.name);
                        templateHTML.setValue(option.template);
                    }}
                />
            </FieldWrapper>

            <fieldset className={'u-margin-top-small'}>
                <label className="u-inline-block">{props.label}</label>
                <span className="u-inline-block">
                    <FieldWrapper label={'Add Template'}>
                        <Input
                            {...newTemplateName}
                        />
                    </FieldWrapper>
                </span>
                <button title={'Add Template'}
                        onClick={() => {
                            setTemplateInitialised(true);
                            templateName.setValue(newTemplateName.value);
                            templateHTML.setValue('');
                            setTemplateSelectValue({});
                        }}
                        className={'u-float-block'}
                >
                    new
                </button>
            </fieldset>

            {templateInitialised &&
                <FieldWrapper label={'Template Name'} className={'u-margin-top-small'}>
                    <Input
                        {...templateName}
                    />
                </FieldWrapper>
            }

            {templateInitialised &&
                <TemplateEditor templateHTML={templateHTML}/>
            }

            {templateInitialised &&
                <button className={"u-margin-top-med"}>save</button>
            }
        </div>
    );
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
        let newStr = `${value.slice(0, position)} {${tag}} ${value.slice(position)}`;
        setValue(newStr);
    }
    
    return {
        value,
        onChange,
        setValue,
        setTag
    }
}