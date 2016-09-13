define([
    'react',
    'ManualOrder/Components/ProductDropdown/Dropdown',
    'ManualOrder/Components/OrderTable'
], function(
    React,
    ProductDropdown,
    OrderTable
) {
    "use strict";
    var OrderForm = React.createClass({
        getInitialState: function () {
            return {
                orderRows: []
            }
        },
        onProductSelected: function (product, sku, quantity) {
            var orderRows = this.state.orderRows.slice();
            var alreadyAddedToForm = orderRows.find(function (row) {
                if (row.sku === sku) {
                    row.quantity += quantity;
                    return true;
                }
            });
            if (! alreadyAddedToForm) {
                orderRows.push({product: product, sku: sku, quantity: quantity, price: 0});
            }

            this.setState({
                orderRows: orderRows
            });
        },
        render: function () {
            return (
                <div className="order-form-wrapper">
                    <h2>Search for Products to Add</h2>
                    <ProductDropdown onOptionSelected={this.onProductSelected} />
                    <OrderTable orderRows={this.state.orderRows} />
                </div>
            );
        }
    });

    return OrderForm;
});