import React from 'react';
import ProductDropdown from 'Common/Components/ProductDropdown/Dropdown';
import OrderTable from 'ManualOrder/Components/OrderTable';
import Select from 'Common/Components/Select';
    

    var RootComponent = React.createClass({
        getInitialState: function () {
            return {
                selectedCurrency: this.props.utilities.currency.getCurrencies()[0]
            }
        },
        getCurrencyOptions: function () {
            return this.props.utilities.currency.getCurrencies();
        },
        onCurrencyChanged: function (newCurrency) {
            this.setState({
                selectedCurrency: newCurrency
            })
        },
        getChildContext: function() {
            return {
                carrierUtils: this.props.utilities.carrier,
                currencyUtils: this.props.utilities.currency,
                imageUtils: this.props.utilities.image
            };
        },
        getOrderData: function (orderData) {
            this.setState({
                order: orderData
            }, function(){this.props.onCreateOrder()});
        },
        render: function () {
            return (
                <div className="order-form-wrapper">
                    <h2>Search for Products to Add</h2>
                    <div className="form-row">
                        <ProductDropdown />
                        <div className="currency-dropdown-wrapper">
                            <span className="currency-label">Currency</span>
                            <Select filterable={true} options={this.getCurrencyOptions()} selectedOption={this.state.selectedCurrency} onOptionChange={this.onCurrencyChanged}/>
                        </div>
                    </div>
                    <OrderTable currency={this.state.selectedCurrency} getOrderData={this.getOrderData}/>
                </div>
            );
        }
    });

    RootComponent.childContextTypes = {
        carrierUtils: React.PropTypes.object,
        currencyUtils: React.PropTypes.object,
        imageUtils: React.PropTypes.object
    };

    export default RootComponent;
