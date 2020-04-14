import PropTypes from 'prop-types';
import React from 'react';
import ProductDropdown from 'Product/Components/ProductDropdown/Dropdown';
import OrderTable from 'ManualOrder/Components/OrderTable';
import Select from 'Common/Components/Select';


class RootComponent extends React.Component {
    state = {
        selectedCurrency: (() => {
            const selectedCurrency = this.props.utilities.currency.getCurrencies().find((currency) => {
                return currency.selected;
            });

            return selectedCurrency || this.props.utilities.currency.getCurrencies()[0];
        })()
    };

    getCurrencyOptions = () => {
        return this.props.utilities.currency.getCurrencies();
    };

    onCurrencyChanged = (newCurrency) => {
        this.setState({
            selectedCurrency: newCurrency
        })
    };

    getChildContext() {
        return {
            carrierUtils: this.props.utilities.carrier,
            currencyUtils: this.props.utilities.currency,
            imageUtils: this.props.utilities.image
        };
    }

    getOrderData = (orderData) => {
        this.setState({
            order: orderData
        }, function(){this.props.onCreateOrder()});
    };

    render() {
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
                <OrderTable
                    currency={this.state.selectedCurrency}
                    getOrderData={this.getOrderData}
                    orderItems={this.props.utilities.orderItems}
                />
            </div>
        );
    }
}

RootComponent.childContextTypes = {
    carrierUtils: PropTypes.object,
    currencyUtils: PropTypes.object,
    imageUtils: PropTypes.object
};

export default RootComponent;
