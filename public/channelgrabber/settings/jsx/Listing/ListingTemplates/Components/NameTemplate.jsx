import React from 'react';
import Input from 'Common/Components/Input';
import FieldWrapper from 'Common/Components/FieldWrapper';

export default function NameTemplate(props) {
    return (
        <FieldWrapper label={'Name Template'}>
            <Input
                onChange={props.onChange}
                value={props.value}
            />
        </FieldWrapper>
    );
}