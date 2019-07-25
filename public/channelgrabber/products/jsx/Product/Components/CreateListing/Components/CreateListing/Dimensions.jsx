import React from 'react';
import {Field} from 'redux-form';
import Input from 'Common/Components/Input';
import VariationTable from './VariationTable';
import Validators from '../../Validators';
import fieldService from 'Product/Components/CreateListing/Service/field';

var dimensions = [
    {
        "name": "weight",
        "displayTitle": "Weight"
    },
    {
        "name": "width",
        "displayTitle": "Width"
    },
    {
        "name": "height",
        "displayTitle": "Height"
    },
    {
        "name": "length",
        "displayTitle" : "Depth"
    }
];

var channelDimensionsValidatorMap = {
    "big-commerce" : {
        "weight": Validators.required
    }
};

function DimensionColumn(props) {
    return <td>
        <Field
            name={`dimensions.${fieldService.getVariationIdWithPrefix(props.variation.id)}.${props.dimension.name}`}
            component={props.component}
            validate={props.validatorsForDimensionAndChannel}
            dimensionName={props.dimension.name}
            variation={props.variation}
        />
    </td>;
}

class DimensionsComponent extends React.Component {
    static defaultProps = {
        variationsDataForProduct: [],
        product: {},
        images: true,
        attributeNames: [],
        attributeNameMap: {},
        change: function () {},
        initialDimensions: {},
        accounts: {},
        massUnit: null,
        lengthUnit: null,
        variationImages: {}
    };

    state = {
        touchedDimensions: {}
    };

    componentWillReceiveProps(newProps) {
        if (Object.keys(this.props.initialDimensions).length > 0) {
            return;
        }

        this.setTouchedDimensionsFromInitialDimensions(newProps);
    }

    setTouchedDimensionsFromInitialDimensions = (props) => {
        var touchedDimensions = {};
        dimensions.map(dimension => {
            touchedDimensions[dimension.name] = {};
            this.props.variationsDataForProduct.map(variation => {
                var isTouched = false;
                if (props.initialDimensions[variation.sku] && props.initialDimensions[variation.sku][dimension.name]) {
                    isTouched = true;
                }
                touchedDimensions[dimension.name][variation.sku] = isTouched;
            });
        });

        this.setState({
            touchedDimensions: touchedDimensions
        });
    };

    renderDimensionHeaders = () => {
        let self = this;
        return dimensions.map(function (identifier) {
            let label = identifier.displayTitle;
            let units = (identifier.name == 'weight' ? self.props.massUnit : self.props.lengthUnit);
            label += ' (' + units + ')';
            return <th>{label}</th>;
        });
    };

    renderDimensionColumns = (variation) => {
        return dimensions.map(dimension => {
            var accounts = this.props.accounts;
            let validatorsForDimensionAndChannel = this.getValidatorsForDimensionAndChannel(accounts, dimension);
            return (<DimensionColumn
                variation={variation}
                dimension={dimension}
                component={this.renderInputComponent}
                validatorsForDimensionAndChannel={validatorsForDimensionAndChannel}
            />)
        });
    };

    renderInputComponent = (field) => {
        var errors = field.meta.error && field.meta.dirty ? [field.meta.error] : [];

        let onChange = (value) => {
            this.onInputChange(field.input, field.dimensionName, field.variation.sku, value);
        };

        return <Input
            {...field.input}
            onChange={onChange}
            errors={errors}
            className={"product-dimension-input"}
            errorBoxClassName={"product-input-error"}
            inputType={"number"}
        />;
    };

    onInputChange = (input, dimension, sku, value) => {
        input.onChange(value.target.value);
        if (this.isFirstVariationRow(sku)) {
            this.copyDimensionFromFirstRowToUntouchedRows(dimension, sku, value.target.value);
        } else {
            this.markDimensionAsTouchedForSku(sku, dimension);
        }
    };

    isFirstVariationRow = (sku) => {
        if (sku == this.props.variationsDataForProduct[0].sku) {
            return true;
        }
        return false;
    };

    copyDimensionFromFirstRowToUntouchedRows = (dimension, sku, value) => {
        this.props.variationsDataForProduct.map(variation => {
            if (sku == variation.sku) {
                return;
            }
            if (dimension in this.state.touchedDimensions
                && variation.sku in this.state.touchedDimensions[dimension]
                && this.state.touchedDimensions[dimension][variation.sku]) {
                return;
            }
            this.props.change("dimensions." + variation.id + "." + dimension, value);
        });
    };

    markDimensionAsTouchedForSku = (sku, dimension) => {
        if (this.state.touchedDimensions[dimension][sku]) {
            return;
        }

        var touchedDimensions = Object.assign({}, this.state.touchedDimensions);
        touchedDimensions[dimension][sku] = true;
        this.setState({
            touchedDimensions: touchedDimensions
        });
    };

    render() {
        return <VariationTable
            sectionName={"dimensions"}
            variationsDataForProduct={this.props.variationsDataForProduct}
            product={this.props.product}
            showImages={true}
            renderImagePicker={false}
            attributeNames={this.props.attributeNames}
            attributeNameMap={this.props.attributeNameMap}
            renderCustomTableHeaders={this.renderDimensionHeaders}
            renderCustomTableRows={this.renderDimensionColumns}
            variationImages={this.props.variationImages}
        />;
    }

    getValidatorsForDimensionAndChannel = (accounts, dimension) => {
        if (dimension == undefined) {
            return;
        }
        for (var key in accounts) {
            var account = accounts[key];
            if (channelDimensionsValidatorMap[account.channel] && channelDimensionsValidatorMap[account.channel][dimension.name]) {
                return [channelDimensionsValidatorMap[account.channel][dimension.name]];
            }
        }

        return undefined;
    };
}

export default DimensionsComponent;

