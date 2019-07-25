import React from 'react';
import {Field} from 'redux-form';
import CurrencyInput from 'Common/Components/CurrencyInput';
import VariationTable from './VariationTable';
import Validators from '../../Validators';
import fieldService from 'Product/Components/CreateListing/Service/field';

class ProductPriceComponent extends React.Component {
    static defaultProps = {
        variationsDataForProduct: [],
        product: {},
        images: true,
        attributeNames: [],
        attributeNameMap: {},
        change: function () {},
        accounts: {},
        initialPrices: {},
        touchedPrices: {}
    };

    componentWillReceiveProps(newProps) {
        if (Object.keys(this.props.initialPrices).length > 0) {
            return;
        }

        this.setTouchedPricesFromInitialPrices(newProps);
    }

    setTouchedPricesFromInitialPrices = (props) => {
        var touchedPrices = {};

        props.accounts.map(function(account) {
            var touchedPricesForAccount = {};
            props.variationsDataForProduct.map(function(variation) {
                var isTouched = false;
                if (props.initialPrices[variation.id] && props.initialPrices[variation.id][account.id]) {
                    isTouched = true;
                }
                touchedPricesForAccount[variation.id] = isTouched;
            });
            touchedPrices[account.id] = touchedPricesForAccount
        });

        this.setState({
            touchedPrices: touchedPrices
        });
    };

    renderPriceHeaders = () => {
        return this.props.accounts.map(function (account) {
            return <th
                className={"account-header with-title"}
                title={account.displayName}
            >
                {account.displayName}
            </th>;
        });
    };

    renderPriceColumns = (variation) => {
        return this.props.accounts.map(function (account) {
            return (<td>
                <Field
                    name={`prices.${fieldService.getVariationIdWithPrefix(variation.id)}.account.id`}
                    component={this.renderInputComponent}
                    sku={variation.sku}
                    accountId={account.id}
                    validate={Validators.required}
                />
            </td>)
        }.bind(this));
    };

    renderInputComponent = (field) => {
        return <CurrencyInput
            {...field.input}
            onChange={this.onInputChange.bind(this, field.input, field.accountId, field.sku)}
            currency={this.props.currency}
            title="The price of the variation on the channel"
            min={0}
            className={(Validators.shouldShowError(field) ? 'error' : null)}
        />;
    };

    onInputChange = (input, accountId, sku, value) => {
        input.onChange(value.target.value);
        if (this.isFirstVariationRow(sku)) {
            this.copyPriceFromFirstRowToUntouchedRows(accountId, sku, value.target.value);
        } else {
            this.markPriceAsTouchedForSkuAndAccount(sku, accountId);
        }
    };

    copyPriceFromFirstRowToUntouchedRows = (accountId, sku, value) => {
        this.props.variationsDataForProduct.map(function (variation) {
            if (sku == variation.sku) {
                return;
            }
            if (accountId in this.state.touchedPrices
                && variation.sku in this.state.touchedPrices[accountId]
                && this.state.touchedPrices[accountId][variation.sku]) {
                return;
            }
            this.props.change("prices." + variation.id + "." + accountId, value);
        }.bind(this));
    };

    isFirstVariationRow = (sku) => {
        if (sku == this.props.variationsDataForProduct[0].sku) {
            return true;
        }
        return false;
    };

    markPriceAsTouchedForSkuAndAccount = (sku, accountId) => {
        if (this.state.touchedPrices[accountId][sku]) {
            return;
        }

        var touchedPrices = Object.assign({}, this.state.touchedPrices);
        touchedPrices[accountId][sku] = true;
        this.setState({
            touchedPrices: touchedPrices
        });
    };

    render() {
        return <VariationTable
            sectionName={"prices"}
            variationsDataForProduct={this.props.variationsDataForProduct}
            product={this.props.product}
            showImages={true}
            renderImagePicker={false}
            attributeNames={this.props.attributeNames}
            attributeNameMap={this.props.attributeNameMap}
            renderCustomTableHeaders={this.renderPriceHeaders}
            renderCustomTableRows={this.renderPriceColumns}
            variationImages={this.props.variationImages}
        />;
    }
}

export default ProductPriceComponent;

