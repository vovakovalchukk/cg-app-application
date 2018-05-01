define([
    'react',
    'redux-form',
    'Common/Components/Input',
    './VariationTable'
], function(
    React,
    ReduxForm,
    Input,
    VariationTable
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
        renderDimensionHeaders: function () {
            return dimensions.map(function (identifier) {
                return <th>{identifier.displayTitle}</th>;
            });
        },
        renderDimensionColumns: function (variation) {
            return dimensions.map(function (dimension) {
                return (<td>
                    <Field
                        name={"dimensions." + variation.sku + "." + dimension.name}
                        component={this.renderInputComponent}
                        validate={dimension.validate ? [dimension.validate] : undefined}
                        dimensionName={dimension.name}
                        variation={variation}
                    />
                </td>)
            }.bind(this));
        },
        renderInputComponent: function(field) {
            var errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];
            return <Input
                {...field.input}
                name={field.input.name}
                value={field.input.value}
                onChange={this.onInputChange.bind(this, field.input, field.dimensionName, field.variation.sku)}
                errors={errors}
                className={"product-dimension-input"}
                errorBoxClassName={"product-input-error"}
                inputType={"number"}
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
            return <VariationTable
                sectionName={"dimensions"}
                variationsDataForProduct={this.props.variationsDataForProduct}
                product={this.props.product}
                images={true}
                renderImagePicker={false}
                attributeNames={this.props.attributeNames}
                attributeNameMap={this.props.attributeNameMap}
                renderCustomTableHeaders={this.renderDimensionHeaders}
                renderCustomTableRows={this.renderDimensionColumns}
            />;
        }
    });

    return DimensionsComponent;
});
