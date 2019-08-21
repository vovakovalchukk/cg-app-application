import React from 'react';
import {Field} from 'redux-form';
import Input from 'Common/Components/Input';
import Checkbox from 'Common/Components/Checkbox';
import VariationTable from './VariationTable';
import fieldService from 'Product/Components/CreateListing/Service/field';

var inputTypeComponents = {
    "checkbox": Checkbox
};

var identifiers = [
    {
        "name": "ean",
        "displayTitle": "EAN (European barcode)",
        "type": "number",
        "validate": function(value) {
            if (!value) {
                return undefined;
            }
            if (value.length < 8 || value.length > 13) {
                return 'Must be between 8 and 13 digits long';
            }
            return undefined;
        },
        "normalize": function(value, previousValue) {
            if (value.length > 14) {
                return previousValue;
            }
            return value;
        }
    },
    {
        "name": "upc",
        "displayTitle": "UPC (Widely used in NA)",
        "type": "number",
        "validate": function(value) {
            if (!value) {
                return undefined;
            }
            if (value.length < 8 || value.length > 13) {
                return 'Must be between 8 and 13 digits long';
            }
            return undefined;
        },
        "normalize": function(value, previousValue) {
            if (value.length > 13) {
                return previousValue;
            }
            return value;
        }
    },
    {
        "name": "mpn",
        "displayTitle": "MPN (if applicable)"
    },
    {
        "name": "isbn",
        "displayTitle": "ISBN (if applicable)",
        "validate": function(value) {
            if (!value) {
                return undefined;
            }
            if (value.length !== 13 && value.length !== 10) {
                return 'Must be exactly 10 or 13 digits long';
            }
            return undefined;
        },
        "normalize": function(value, previousValue) {
            if (value.length > 13) {
                return previousValue;
            }
            return value;
        },
    },
    {
        "name": "barcodeNotApplicable",
        "display": "Does not apply",
        "type": "checkbox"
    },
];

let Identifier = props => {
    let {inputComponent, variation, identifier} = props;
    let variationIdWithPrefix = fieldService.getVariationIdWithPrefix(variation.id);
    return <td className={'u-width-120px'}>
        <Field
            name={`identifiers.${variationIdWithPrefix}.${identifier.name}`}
            component={inputComponent}
            validate={identifier.validate ? [identifier.validate] : undefined}
            normalize={identifier.normalize ? identifier.normalize : value => value}
            inputType={identifier.type ? identifier.type : 'input'}
        />
    </td>;
};

class ProductIdentifiers extends React.Component {
    static defaultProps = {
        variationsDataForProduct: [],
        product: {},
        attributeNames: [],
        attributeNameMap: {},
        renderImagePicker: true,
        shouldRenderStaticImagesFromVariationValues: false,
        containerCssClasses: '',
        tableCssClasses: '',
        renderStaticImageFromFormValues: false
    };

    renderHeader = identifier => {
        return <th
            title={identifier.displayTitle}
            className={"with-title"}
        >
            {identifier.display || identifier.name.toUpperCase()}
        </th>;
    };

    renderIdentifierHeaders = () => {
        return identifiers.map(this.renderHeader);
    };

    renderIdentifierColumns = (variation) => {
        return identifiers.map((identifier) => {
            return <Identifier
                identifier={identifier}
                variation={variation}
                inputComponent={this.renderInputComponent}
            />
        });
    };

    renderInputComponent = (field) => {
        const errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];
        const InputForType = this.getInput(field);

        const onChangeHandler = value => {
            this.onInputChange(field.input, value );
        };

        return <InputForType
            {...field.input}
            onChange={onChangeHandler}
            errors={errors}
            className={"product-identifier-input"}
            errorBoxClassName={"product-input-error"}
            inputType={"number"}
        />
    };

    getInput = (field) => {
        if(typeof inputTypeComponents[field.inputType] !== 'undefined' ){
            return inputTypeComponents[field.inputType]
        }
        return Input;
    };

    onInputChange = (input, value) => {
        input.onChange(value);
    };

    render() {
        return (
            <VariationTable
                sectionName={"identifiers"}
                variationsDataForProduct={this.props.variationsDataForProduct}
                product={this.props.product}
                showImages={true}
                attributeNames={this.props.attributeNames}
                attributeNameMap={this.props.attributeNameMap}
                renderImagePicker={this.props.renderImagePicker}
                shouldRenderStaticImagesFromVariationValues={this.props.shouldRenderStaticImagesFromVariationValues}
                containerCssClasses={this.props.containerCssClasses}
                tableCssClasses={this.props.tableCssClasses}
                renderCustomTableHeaders={this.renderIdentifierHeaders}
                renderCustomTableRows={this.renderIdentifierColumns}
                renderStaticImageFromFormValues={this.props.renderStaticImageFromFormValues}
            />
        );
    }
}

export default ProductIdentifiers;