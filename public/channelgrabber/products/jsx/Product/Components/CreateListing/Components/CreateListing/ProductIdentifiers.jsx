define([
    'react',
    'redux-form',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/Shared/ImageDropDown'
], function(
    React,
    ReduxForm,
    Input,
    ImageDropDown
) {
    "use strict";

    var FormSection = ReduxForm.FormSection;
    var Field = ReduxForm.Field;

    var identifiers = [
        {
            "name": "ean",
            "displayTitle": "EAN (European barcode)",
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
            }
        },
        {
            "name": "upc",
            "displayTitle": "UPC (Widely used in NA)",
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
                images: true,
                attributeNames: [],
                attributeNameMap: {},
            }
        },
        renderImageHeader: function() {
            if (!this.props.images) {
                return;
            }
            return <th>Image</th>;
        },
        renderAttributeHeaders: function () {
            return this.props.attributeNames.map(function(attributeName) {
                var attributeNameText = this.props.attributeNameMap[attributeName] ? this.props.attributeNameMap[attributeName] : attributeName;
                return <th>
                    {attributeNameText}
                </th>;
            }.bind(this));
        },
        renderIdentifierReaders: function () {
            return identifiers.map(function (identifier) {
                return <th
                    title={identifier.displayTitle}
                    className={"with-title"}
                >
                    {identifier.name.toUpperCase()}
                </th>;
            });
        },
        renderVariationRows: function () {
            return this.props.variationsDataForProduct.map(function(variation) {
                return <tr>
                    {this.renderImageColumn(variation)}
                    <td>{variation.sku}</td>
                    {this.renderAttributeColumns(variation)}
                    {this.renderIdentifierColumns(variation)}
                </tr>
            }.bind(this));
        },
        renderImageColumn: function(variation) {
            if (!this.props.images) {
                return;
            }
            if (this.props.product.images == 0) {
                return <td>No images available</td>
            }

            return (<td>
                <Field
                    name={variation.sku + ".imageId"}
                    component={this.renderImageField.bind(this, variation)}
                />
            </td>);
        },
        renderImageField: function(variation, field) {
            var selected = (variation.images.length > 0 ? variation.images[0] : this.props.product.images[0]);
            return <ImageDropDown
                selected={selected}
                autoSelectFirst={false}
                images={this.props.product.images}
                onChange={this.onImageSelected.bind(this, field)}
            />
        },
        onImageSelected: function(field, image) {
            this.onInputChange(field.input, image.target.value);
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
        },
        renderIdentifierColumns: function (variation) {
            return identifiers.map(function (identifier) {
                return (<td>
                    <Field
                        name={variation.sku + "." + identifier.name}
                        component={this.renderInputComponent}
                        validate={identifier.validate ? [identifier.validate] : undefined}
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
                inputType={"number"}
            />;
        },
        onInputChange: function(input, value) {
            input.onChange(value);
        },
        render: function() {
            return (
                <FormSection name="identifiers">
                    <div className={"variation-picker"}>
                        <table>
                            <thead>
                            <tr>
                                {this.renderImageHeader()}
                                <th>SKU</th>
                                {this.renderAttributeHeaders()}
                                {this.renderIdentifierReaders()}
                            </tr>
                            </thead>
                            {this.renderVariationRows()}
                        </table>
                    </div>
                </FormSection>
            );
        }
    });

    return ProductIdentifiers;
});
