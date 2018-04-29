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
                change: function () {}
            }
        },
        getInitialState: function() {
            return {
                touchedDimensions: {}
            }
        },
        componentDidMount: function() {
            var touchedDimensions = {};
            dimensions.map(function (dimension) {
                touchedDimensions[dimension.name] = {};
                this.props.variationsDataForProduct.map(function (variation) {
                    touchedDimensions[dimension.name][variation.sku] = false;
                }.bind(this));
            }.bind(this));
            this.setState({
                touchedDimensions: touchedDimensions
            });
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
                    name={"identifiers." + variation.sku + ".imageId"}
                    component={this.renderImageField}
                />
            </td>);
        },
        renderImageField: function(field) {
            var image = this.findSelectedImageForVariation(field.input.value);
            return (
                <div className="image-dropdown-target">
                    <div className="react-image-picker">
                        <span className="react-image-picker-image">
                            <img src={image.url}/>
                        </span>
                    </div>
                </div>

            );
        },
        findSelectedImageForVariation: function(imageId) {
            var selectedImage = {url: ""};
            if (!imageId) {
                return selectedImage;
            }
            this.props.product.images.map(function(image) {
                if (image.id == imageId) {
                    selectedImage = image;
                }
            });
            return selectedImage;
        },
        renderAttributeColumns: function(variation) {
            return this.props.attributeNames.map(function(attributeName) {
                return <td>{variation.attributeValues[attributeName]}</td>
            });
        },
        renderDimensionColumns: function (variation) {
            return dimensions.map(function (dimension) {
                return (<td>
                    <Field
                        name={"dimensions." + variation.sku + "." + dimension.name}
                        component={this.renderInputComponent.bind(this, dimension.name)}
                        validate={dimension.validate ? [dimension.validate] : undefined}
                    />
                </td>)
            }.bind(this));
        },
        renderInputComponent: function(dimension, field) {
            var errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];
            return <Input
                name={field.input.name}
                value={field.input.value}
                onChange={this.onInputChange.bind(this, field.input, dimension)}
                errors={errors}
            />;
        },
        onInputChange: function(input, dimension, value) {
            input.onChange(value.target.value);
            this.props.variationsDataForProduct.map(function (variation) {
                if (dimension in this.state.touchedDimensions
                    && variation.sku in this.state.touchedDimensions[dimension]
                    && this.state.touchedDimensions[dimension][variation.sku]) {
                    return;
                }
                this.props.change("dimensions." + variation.sku + "." + dimension, value.target.value);
            }.bind(this));
        },
        render: function() {
            return (
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
            );
        }
    });

    return DimensionsComponent;
});
