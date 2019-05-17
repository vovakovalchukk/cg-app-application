import React from 'react';
import Input from 'Common/Components/Input';
import FieldWrapper from 'Common/Components/FieldWrapper';

export default function NewTemplate(props) {
    return (
        <FieldWrapper label={'Add Template'}>
            <Input
                onChange={props.onChange}
                value={props.value}
            />
        </FieldWrapper>
    );
}