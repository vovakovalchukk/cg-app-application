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

    var dimensions = [
        {
            "name": "weight",
            "displayTitle": "Weight (kg)"
        },
        {
            "name": "width",
            "displayTitle": "Width (cm)"
        },
        {
            "name": "height",
            "displayTitle": "Height (cm)"
        },
        {
            "name": "length",
            "displayTitle" : "Depth (cm)"
        }
    ];

    var DimensionsComponent = React.createClass({
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
        renderDimensionHeaders: function () {
            return dimensions.map(function (identifier) {
                return <th>{identifier.displayTitle}</th>;
            });
        },
        renderVariationRows: function () {
            return this.props.variationsDataForProduct.map(function(variation) {
                return <tr>
                    {this.renderImageColumn(variation)}
                    <td>{variation.sku}</td>
                    {this.renderAttributeColumns(variation)}
                    {this.renderDimensionColumns(variation)}
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
        renderDimensionColumns: function (variation) {
            return dimensions.map(function (identifier) {
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
            />;
        },
        onInputChange: function(input, value) {
            input.onChange(value);
        },
        render: function() {
            return (
                <FormSection name="dimensions">
                    <div className={"variation-picker"}>
                        <table>
                            <thead>
                            <tr>
                                {this.renderImageHeader()}
                                <th>SKU</th>
                                {this.renderAttributeHeaders()}
                                {this.renderDimensionHeaders()}
                            </tr>
                            </thead>
                            {this.renderVariationRows()}
                        </table>
                    </div>
                </FormSection>
            );
        }
    });

    return DimensionsComponent;
});
