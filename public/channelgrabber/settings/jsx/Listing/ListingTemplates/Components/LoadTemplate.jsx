import React from 'react';
import Select from 'Common/Components/Select';
import FieldWrapper from 'Common/Components/FieldWrapper';

export default function LoadTemplate(props) {
    let options = [
        {name: 'option1', label: 'option2'}
    ];

    return (
        <FieldWrapper label={'Load Template'}>
            <Select
                options={options}
                autoSelectFirst={false}
                title={'choose your template to load'}
                customOptions={true}
                onOptionChange={props.onOptionChange}
                selectedOption={props.selectedOption}
            />
        </FieldWrapper>
    );
}