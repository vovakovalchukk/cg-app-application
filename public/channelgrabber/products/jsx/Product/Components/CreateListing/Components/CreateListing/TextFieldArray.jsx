import React from 'react';
import {Field, FieldArray} from 'redux-form';
import styled from 'styled-components';

const FieldRow = styled.div`
  float: left;
`;
const Input = styled.input`
  width: 450px;
`;

const FieldInput = ({ input, label, type, meta: { touched, error } }) => (
    <span>
        <div>
            <Input {...input} type={type} placeholder={label} />
            {touched && error && <span>{error}</span>}
        </div>
    </span>
);

export default ({fields, displayTitle, itemPlaceholder, meta, itemLimit}) => {
    console.log('fields',fields);
    if (!fields.length){
        fields.push();
    }

    let addClick = () => {
        if(fields.length >= 5){
            return;
        }
        fields.push({});
    };

    return (
        <fieldset className="input-container">
            <span className={"inputbox-label"}>{displayTitle}</span>
            <FieldRow>
                {fields.map((item, index) => {
                    return <div className={'u-flex-center u-margin-top-xsmall'}>
                        <span>
                            <Field
                                name={`${item}.bullet${index+1}`}
                                type="text"
                                component={FieldInput}
                                label={`${itemPlaceholder} ${index+1}`}
                            />
                        </span>

                        <span>
                            <button
                                type="button"
                                title={`Remove ${itemPlaceholder}`}
                                onClick={() => fields.remove(index)}
                            >
                                Remove
                            </button>
                        </span>
                    </div>
                })}
                <div className={'u-margin-top-small'}>
                    <button type="button" onClick={addClick}>Add {itemPlaceholder}</button>
                </div>
            </FieldRow>

        </fieldset>
    )
};