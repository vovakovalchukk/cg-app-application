import React from 'react';
import {Field, FieldArray} from 'redux-form';
import styled from 'styled-components';
import validators from 'Product/Components/CreateListing/Validators';

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
            {touched && error && <div className={'u-color-red'}>{error}</div>}
        </div>
    </span>
);

const RemoveButton = ({buttonOnClick, buttonTitle}) => {
    return (
        <span>
            <button
                type="button"
                title={buttonTitle}
                onClick={buttonOnClick}
            >
                Remove
            </button>
        </span>
    )
};


export default ({fields, displayTitle, itemPlaceholder, meta, itemLimit, itemPrefix, maxCharLength}) => {
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
                    return(
                        <div className={'u-flex-v-center u-margin-top-xsmall'}>
                            <span>
                                <Field
                                    type="text"
                                    name={`${item}.field${index+1}`}
                                    component={FieldInput}
                                    label={`${itemPlaceholder} ${index+1}`}
                                    validate={[validators[`maxLength${maxCharLength}`]]}
                                />
                            </span>


                            {index > 0 && (
                                <RemoveButton
                                    buttonTitle={`Remove ${itemPlaceholder}`}
                                    buttonOnClick={() => fields.remove(index)}
                                />
                            )}
                        </div>
                    )
                })}

                {fields.length < itemLimit && (
                    <div className={'u-margin-top-small'}>
                        <button type="button" onClick={addClick}>Add {itemPlaceholder}</button>
                    </div>
                )}
            </FieldRow>
        </fieldset>
    )
};