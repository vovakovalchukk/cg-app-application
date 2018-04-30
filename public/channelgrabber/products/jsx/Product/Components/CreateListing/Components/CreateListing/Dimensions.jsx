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

    var validateNumber = function (value) {
        if (isNaN(Number(value))) {
            return 'Must be a number';
        }
        return undefined;
    };

    var dimensions = [
        {
            "name": "weight",
            "displayTitle": "Weight (kg)",
            "validate": validateNumber
        },
        {
            "name": "width",
            "displayTitle": "Width (cm)",
            "validate": validateNumber
        },
        {
            "name": "height",
            "displayTitle": "Height (cm)",
            "validate": validateNumber
        },
        {
            "name": "length",
            "displayTitle" : "Depth (cm)",
            "validate": validateNumber
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
                change: function () {},
                initialDimensions: {}
            }
        },
        getInitialState: function() {
            return {
                touchedDimensions: {}
            }
        },
        componentWillReceiveProps: function(newProps) {
            if (Object.keys(this.props.initialDimensions).length > 0) {
                return;
            }

            this.setTouchedDimensionsFromInitialDimensions(newProps);
        },
        setTouchedDimensionsFromInitialDimensions: function(props) {
            var touchedDimensions = {};
            dimensions.map(function (dimension) {
                touchedDimensions[dimension.name] = {};
                this.props.variationsDataForProduct.map(function (variation) {
                    var isTouched = false;
                    if (props.initialDimensions[variation.sku] && props.initialDimensions[variation.sku][dimension.name]) {
                        isTouched = true;
                    }
                    touchedDimensions[dimension.name][variation.sku] = isTouched;
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
                        component={this.renderInputComponent.bind(this, dimension.name, variation.sku)}
                        validate={dimension.validate ? [dimension.validate] : undefined}
                    />
                </td>)
            }.bind(this));
        },
        renderInputComponent: function(dimension, sku, field) {
            var errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];
            return <Input
                name={field.input.name}
                value={field.input.value}
                onChange={this.onInputChange.bind(this, field.input, dimension, sku)}
                errors={errors}
            />;
        },
        onInputChange: function(input, dimension, sku, value) {
            input.onChange(value.target.value);
            if (this.isFirstVariationRow(sku)) {
                this.props.variationsDataForProduct.map(function (variation) {
                    if (sku == variation.sku) {
                        return;
                    }
                    if (dimension in this.state.touchedDimensions
                        && variation.sku in this.state.touchedDimensions[dimension]
                        && this.state.touchedDimensions[dimension][variation.sku]) {
                        return;
                    }
                    this.props.change("dimensions." + variation.sku + "." + dimension, value.target.value);
                }.bind(this));
            } else {
                this.markDimensionAsTouchedForSku(sku, dimension);
            }
        },
        isFirstVariationRow: function(sku) {
            if (sku == this.props.variationsDataForProduct[0].sku) {
                return true;
            }
            return false;
        },
        markDimensionAsTouchedForSku: function(sku, dimension) {
            if (this.state.touchedDimensions[dimension][sku]) {
                return;
            }

            var touchedDimensions = Object.assign({}, this.state.touchedDimensions);
            touchedDimensions[dimension][sku] = true;
            this.setState({
                touchedDimensions: touchedDimensions
            });
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
