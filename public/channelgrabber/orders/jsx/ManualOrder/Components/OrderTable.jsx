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
                    row.quantity += parseInt(quantity);
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
        onRowRemove: function (sku) {
            var orderRows = this.state.orderRows.filter(function (row) {
                return row.sku !== sku;
            });
            this.setState({
                orderRows: orderRows
            });
        },
        onSkuChanged: function () {
            console.log('SKU change');
        },
        onPriceChanged: function (sku, price) {
            this.updateOrderRow(sku, 'price', price);
        },
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateOrderRow(sku, 'quantity', quantity);
        },
        updateOrderRow: function (sku, key, value) {
            var orderRows = this.state.orderRows.slice();
            orderRows.forEach(function (row) {
                if (row.sku === sku) {
                    row[key] = value;
                }
            });
            this.setState({
                orderRows: orderRows
            });
        },
        getOrderRowsMarkup: function () {
            return (
                this.state.orderRows.map(function (row) {
                    return (
                        <OrderRow row={row}
                                  onSkuChange={this.onSkuChanged}
                                  onStockQuantityUpdate={this.onStockQuantityUpdated}
                                  onPriceChange={this.onPriceChanged}
                                  onRowRemove={this.onRowRemove}
                        />
                    )
                }.bind(this))
            );
        },
        getDiscountMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }
            return <a>Add Discount</a>
        },
        getSubtotalMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }
            var currency = "£";
            var rowTotal = 0;
            this.state.orderRows.forEach(function (row) {
                rowTotal += parseFloat(row.price * row.quantity);
            });
            return (
                <div>
                    <span className="subtotal-label">Subtotal</span>
                    <span className="subtotal-value">{currency + rowTotal.toFixed(2)}</span>
                </div>
            );
        },
        getShippingMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }
        },
        getOrderTotalMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }
            var currency = "£";
            var orderTotal = 0;
            var shippingTotal = 0;
            this.state.orderRows.forEach(function (row) {
                orderTotal += parseFloat(row.price * row.quantity);
            });
            return (
                <div>
                    <span className="total-label">Total</span>
                    <span className="total-value">{currency + orderTotal.toFixed(2)}</span>
                </div>
            );
        },
        render: function () {
            return (
                <div className="order-table-wrapper">
                    <div className="order-rows-wrapper">{this.getOrderRowsMarkup()}</div>
                    <div className="discount-wrapper">{this.getDiscountMarkup()}</div>
                    <div className="subtotal-wrapper">{this.getSubtotalMarkup()}</div>
                    <div className="shipping-wrapper">{this.getShippingMarkup()}</div>
                    <div className="order-total-wrapper">{this.getOrderTotalMarkup()}</div>
                </div>
            );
        }
    });

    return OrderTable;
});