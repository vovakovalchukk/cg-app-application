define([
    'react',
    'ManualOrder/Components/OrderRow'
], function(
    React,
    OrderRow
) {
    "use strict";
    var OrderTable = React.createClass({
        getInitialState: function () {
            return {
                orderRows: []
            }
        },
        componentDidMount: function () {
            window.addEventListener('productSelection', this.onProductSelected);
        },
        componentWillUnmount: function () {
            window.removeEventListener('productSelection', this.onProductSelected);
        },
        onProductSelected: function (e) {
            var data = e.detail;
            this.addOrderRow(data.product, data.sku, data.quantity);
        },
        addOrderRow: function (product, sku, quantity) {
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
        getOrderRows: function () {
            return (
                this.state.orderRows.map(function (row) {
                    return (
                        <OrderRow row={row} onSkuChange={this.onSkuChanged} onStockQuantityUpdate={this.onStockQuantityUpdated} onPriceChange={this.onPriceChanged}/>
                    )
                }.bind(this))
            );
        },
        onSkuChanged: function () {
            console.log('SKU change');
        },
        onPriceChanged: function (sku, price) {
            var orderRows = this.state.orderRows.slice();
            orderRows.forEach(function (row) {
                if (row.sku === sku) {
                    row.price = price;
                }
            });
            this.setState({
                orderRows: orderRows
            });
        },
        onStockQuantityUpdated: function (sku, quantity) {
            console.log(sku);
            console.log(quantity);
        },
        render: function () {
            return (
                <div className="order-table-wrapper">
                    <div className="order-rows-wrapper">
                        {this.getOrderRows()}
                    </div>
                    <div className="order-footer-wrapper">
                    </div>
                </div>
            );
        }
    });

    return OrderTable;
});