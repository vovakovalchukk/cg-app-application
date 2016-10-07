define([
    'react',
    'ManualOrder/Components/ProductDropdown/Dropdown',
    'ManualOrder/Components/OrderTable',
    'Product/Components/Select'
], function(
    React,
    ProductDropdown,
    OrderTable,
    Select
) {
    "use strict";
    var OrderForm = React.createClass({
        getInitialState: function () {
            return {
                selectedCurrency: {
                    name: 'GBP',
                    value: '£'
                }
            }
        },
        getCurrencyOptions: function () {
            return this.context.currencyUtils.getCurrencies();
        },
        onCurrencyChanged: function (newCurrency) {
            this.setState({
                selectedCurrency: newCurrency
            })
        },
        render: function () {
            return (
                <div className="order-form-wrapper">
                    <h2>Search for Products to Add</h2>
                    <div className="form-row">
                        <ProductDropdown />
                        <div className="currency-dropdown-wrapper">
                            <span className="currency-label">Currency</span>
                            <Select filterable={true} options={this.getCurrencyOptions()} selectedOption={this.state.selectedCurrency} onNewOption={this.onCurrencyChanged}/>
                        </div>
                    </div>
                    <OrderTable currency={this.state.selectedCurrency}/>
                </div>
            );
        }
    });

    OrderForm.contextTypes = {
        currencyUtils: React.PropTypes.object
    };

    return OrderForm;
});