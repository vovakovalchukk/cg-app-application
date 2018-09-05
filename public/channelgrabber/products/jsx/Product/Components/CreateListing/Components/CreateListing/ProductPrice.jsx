define([
    'react',
    'redux-form',
    'Common/Components/Input',
    'Common/Components/CurrencyInput',
    './VariationTable',
    '../../Validators'
], function(
    React,
    ReduxForm,
    Input,
    CurrencyInput,
    VariationTable,
    Validators
) {
    "use strict";

    var Field = ReduxForm.Field;

    var ProductPriceComponent = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                product: {},
                images: true,
                attributeNames: [],
                attributeNameMap: {},
                change: function () {},
                accounts: {},
                initialPrices: {},
                touchedPrices: {}
            }
        },
        componentWillReceiveProps: function(newProps) {
            if (Object.keys(this.props.initialPrices).length > 0) {
                return;
            }

            this.setTouchedPricesFromInitialPrices(newProps);
        },
        setTouchedPricesFromInitialPrices: function(props) {
            var touchedPrices = {};

            props.accounts.map(function(account) {
                var touchedPricesForAccount = {};
                props.variationsDataForProduct.map(function(variation) {
                    var isTouched = false;
                    if (props.initialPrices[variation.sku] && props.initialPrices[variation.sku][account.id]) {
                        isTouched = true;
                    }
                    touchedPricesForAccount[variation.sku] = isTouched;
                });
                touchedPrices[account.id] = touchedPricesForAccount
            });

            this.setState({
                touchedPrices: touchedPrices
            });
        },
        renderPriceHeaders: function () {
            return this.props.accounts.map(function (account) {
                return <th
                    className={"account-header with-title"}
                    title={account.displayName}
                >
                    {account.displayName}
                </th>;
            });
        },
        renderPriceColumns: function (variation) {
            return this.props.accounts.map(function (account) {
                return (<td>
                    <Field
                        name={"prices." + variation.sku + "." + account.id}
                        component={this.renderInputComponent}
                        sku={variation.sku}
                        accountId={account.id}
                        validate={Validators.required}
                    />
                </td>)
            }.bind(this));
        },
        renderInputComponent: function(field) {
            return <CurrencyInput
                {...field.input}
                onChange={this.onInputChange.bind(this, field.input, field.accountId, field.sku)}
                currency={this.props.currency}
                title="The price of the variation on the channel"
                min={0}
                className={(Validators.shouldShowError(field) ? 'error' : null)}
            />;
        },
        onInputChange: function(input, accountId, sku, value) {
            input.onChange(value.target.value);
            if (this.isFirstVariationRow(sku)) {
                this.copyPriceFromFirstRowToUntouchedRows(accountId, sku, value.target.value);
            } else {
                this.markPriceAsTouchedForSkuAndAccount(sku, accountId);
            }
        },
        copyPriceFromFirstRowToUntouchedRows: function(accountId, sku, value) {
            this.props.variationsDataForProduct.map(function (variation) {
                if (sku == variation.sku) {
                    return;
                }
                if (accountId in this.state.touchedPrices
                    && variation.sku in this.state.touchedPrices[accountId]
                    && this.state.touchedPrices[accountId][variation.sku]) {
                    return;
                }
                this.props.change("prices." + variation.sku + "." + accountId, value);
            }.bind(this));
        },
        isFirstVariationRow: function(sku) {
            if (sku == this.props.variationsDataForProduct[0].sku) {
                return true;
            }
            return false;
        },
        markPriceAsTouchedForSkuAndAccount: function(sku, accountId) {
            if (this.state.touchedPrices[accountId][sku]) {
                return;
            }

            var touchedPrices = Object.assign({}, this.state.touchedPrices);
            touchedPrices[accountId][sku] = true;
            this.setState({
                touchedPrices: touchedPrices
            });
        },
        render: function() {
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
    });

    return ProductPriceComponent;
});
