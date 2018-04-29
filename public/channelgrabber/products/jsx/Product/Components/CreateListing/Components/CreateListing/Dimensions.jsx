define([
    'react',
    'redux-form',
    'Common/Components/Input'
], function(
    React,
    ReduxForm,
    Input
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
                selectedProductIdentifiers: {}
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

            var image = this.findSelectedImageForVariation(variation);
            return (<td>
                <div className="image-dropdown-target">
                    <div className="react-image-picker">
                        <span className="react-image-picker-image">
                            <img src={image.url}/>
                        </span>
                    </div>
                </div>
            </td>);
        },
        findSelectedImageForVariation: function(variation) {
            var selectedImage = {url: ""};
            if (variation.sku in this.props.selectedProductIdentifiers) {
                var identifiers = this.props.selectedProductIdentifiers[variation.sku];
                if (identifiers.imageId) {
                    this.props.product.images.map(function(image) {
                        if (image.id == identifiers.imageId) {
                            selectedImage = image;
                        }
                    });
                }
            }
            return selectedImage;
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
