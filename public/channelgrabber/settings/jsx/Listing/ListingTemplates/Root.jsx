import React, {useState} from 'react';
import Select from 'Common/Components/Select';
import Input from 'Common/Components/Input';
import FieldWrapper from 'Common/Components/FieldWrapper';

const RootComponent = props => {
    const templateName = useFormInput('');
    const newTemplateName = useFormInput('');

    const [templateInitialised, setTemplateInitialised] = useState();
    const [templateSelectValue, setTemplateSelectValue] = useState();

    const templateHTML = useTemplateHtml('');

    const options = [
        {name: 'option1', label: 'option1', value: 'option1'},
        {name: 'option2', label: 'option2', value: 'option2'},
        {name: 'option3', label: 'option3', value: 'option3'},
    ];

    return (
        <div>
            <FieldWrapper label={'Load Template'} className={'u-margin-top-small'}>
                <Select
                    options={options}
                    autoSelectFirst={false}
                    title={'choose your template to load'}
                    customOptions={true}
                    selectedOption={templateSelectValue}
                    onOptionChange={(option)=>{
                        console.log('option change... ', option);
                        setTemplateSelectValue(option);
                        setTemplateInitialised(true);
                        console.log('about to set value to.... ', option.name);
                        templateName.setValue(option.name)
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
                <div className={'u-margin-top-med'}>
                    <h3>Ebay Listing Template Designer</h3>
                    <textarea
                        className={'u-margin-top-small'}
                        style={{width:'500px'}}
                        value={templateHTML.value}
                        onChange={templateHTML.onChange}
                    />
                </div>
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
    return {
        value,
        onChange,
        setValue
    }

}