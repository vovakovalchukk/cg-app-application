import React from 'react';
import {Field, FieldArray} from 'redux-form';
import styled from 'styled-components';
import validators from 'Product/Components/CreateListing/Validators';
import RemoveIcon from 'Common/Components/RemoveIcon';

const FieldRow = styled.div`
  float: left;
`;
const Input = styled.input`
  width: 450px;
`;

const FieldInput = ({input, label, type, meta: {touched, error}}) => {
    console.log('status: ', {input, meta});

    return (
        <span>
        <div>
            <Input {...input} type={type} placeholder={label}/>
            {touched && error && <div className={'u-color-red'}>{error}</div>}
        </div>
    </span>
    )

};

const RemoveButton = ({buttonOnClick, buttonTitle, shouldRender}) => {
    if (!shouldRender) {
        return null;
    }
    return (
        <span>
            <RemoveIcon onClick={buttonOnClick} title={buttonTitle}/>
        </span>
    )
};

const AddButton = ({shouldRender, onButtonClick, buttonText}) => {
    if (!shouldRender) {
        return null;
    }
    return (
        <div className={'u-margin-top-small'}>
            <button type="button" onClick={onButtonClick}>{buttonText}</button>
        </div>
    );
};

const TextArrayInput = ({namePrefix, index, itemPlaceholder, validatorMethod}) => {
    return (<span>
        <Field
            type="text"
            name={`${namePrefix}.field${index + 1}`}
            component={FieldInput}
            label={`${itemPlaceholder} ${index + 1}`}
            validate={[validatorMethod]}
        />
    </span>);
};

const TextFieldArray = ({fields, displayTitle, itemPlaceholder, meta, itemLimit, itemPrefix, maxCharLength}) => {
    if (!fields.length) {
        fields.push();
    }

    const addClick = () => {
        if (fields.length >= 5) {
            return;
        }
        fields.push({});
    };

    const maxLengthValidatorMethod = validators.maxLength(maxCharLength);

    console.log('maxLengthValidatorMethod: ', maxLengthValidatorMethod);

    const renderInputRow = (item, index) => {
        return (
            <div className={'u-flex-v-center u-margin-top-xsmall'}>
                <TextArrayInput
                    namePrefix={item}
                    index={index}
                    itemPlaceholder={itemPlaceholder}
                    validatorMethod={maxLengthValidatorMethod}
                />
                <RemoveButton
                    buttonTitle={`Remove ${itemPlaceholder}`}
                    buttonOnClick={() => fields.remove(index)}
                    shouldRender={index > 0}
                />
            </div>
        )
    };

    const renderInputRows = () => {
        return fields.map(renderInputRow);
    };

    return (
        <fieldset className="input-container">
            <span className={"inputbox-label"}>{displayTitle}</span>
            <FieldRow>
                {renderInputRows()}
                <AddButton
                    shouldRender={fields.length < itemLimit}
                    onButtonClick={addClick}
                    buttonText={`Add ${itemPlaceholder}`}
                />
            </FieldRow>
        </fieldset>
    );
};

export default TextFieldArray;