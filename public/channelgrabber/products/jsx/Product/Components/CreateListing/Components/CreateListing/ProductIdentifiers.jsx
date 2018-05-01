define([
    'react',
    'redux-form',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Shared/ImageDropDown',
    './VariationTable'
], function(
    React,
    ReduxForm,
    Input,
    ImageDropDown,
    VariationTable
) {
    "use strict";

    var Field = ReduxForm.Field;

    var identifiers = [
        {
            "name": "ean",
            "displayTitle": "EAN (European barcode)",
            "type": "number",
            "validate": function(value) {
                if (!value) {
                    return undefined;
                }
                if (isNaN(Number(value))) {
                    return 'Must be a number';
                }
                if (value.length != 13) {
                    return 'Must be exactly 13 digits long';
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
                if (isNaN(Number(value))) {
                    return 'Must be a number';
                }
                if (value.length != 12) {
                    return 'Must be exactly 12 digits long';
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
            "displayTitle" : "ISBN (if applicable)"
        }
    ];

    var ProductIdentifiers = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                attributeNames: [],
                attributeNameMap: {},
            }
        },
        renderIdentifierHeaders: function () {
            return identifiers.map(function (identifier) {
                return <th
                    title={identifier.displayTitle}
                    className={"with-title"}
                >
                    {identifier.name.toUpperCase()}
                </th>;
            });
        },
        renderIdentifierColumns: function (variation) {
            return identifiers.map(function (identifier) {
                return (<td>
                    <Field
                        name={variation.sku + "." + identifier.name}
                        component={this.renderInputComponent}
                        validate={identifier.validate ? [identifier.validate] : undefined}
                        normalize={identifier.normalize ? identifier.normalize : value => value}
                        inputType={identifier.type ? identifier.type : 'input'}
                    />
                </td>)
            }.bind(this));
        },
        renderInputComponent: function(field) {
            var errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];
            return <Input
                name={field.input.name}
                value={field.input.value}
                onChange={this.onInputChange.bind(this, field.input)}
                errors={errors}
                className={"product-identifier-input"}
                errorBoxClassName={"product-input-error"}
                inputType={field.inputType}
            />;
        },
        onInputChange: function(input, value) {
            input.onChange(value);
        },
        render: function() {
            return (
                <VariationTable
                    sectionName={"identifiers"}
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    images={true}
                    attributeNames={this.props.attributeNames}
                    attributeNameMap={this.props.attributeNameMap}
                    renderCustomTableHeaders={this.renderIdentifierHeaders}
                    renderCustomTableRows={this.renderIdentifierColumns}
                />
            );
        }
    });

    return ProductIdentifiers;
});
