define([
    'react',
    'ManualOrder/Components/OrderRow',
    'Product/Components/Select'
], function(
    React,
    OrderRow,
    Select
) {
    "use strict";
    var OrderTable = React.createClass({
        getInitialState: function () {
            return {
                shippingOptions: [],
                shippingMethod: {
                    name: "",
                    cost: 0
                },
                discount: {
                    active: false,
                    value: 0
                },
                orderRows: []
            }
        },
        componentDidMount: function () {
            window.addEventListener('productSelection', this.onProductSelected);

            this.shippingOptionsRequest = $.ajax({
                dataType: "json",
                url: '/orders/shippingOptions',
                success: function (response) {
                    var shippingOptions = [{name: 'N/A', value: 0}];
                    this.setState({
                        shippingOptions: shippingOptions.concat(response.options)
                    });
                }.bind(this)
            });
        },
        componentWillUnmount: function () {
            window.removeEventListener('productSelection', this.onProductSelected);

            this.shippingOptionsRequest.abort();
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
        onSkuChanged: function (oldSku, selection) {
            if (selection === undefined || oldSku === selection.value) {
                return;
            }
            this.updateOrderRow(oldSku, 'sku', selection.value);
        },
        onPriceChanged: function (sku, price) {
            this.updateOrderRow(sku, 'price', price);
        },
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateOrderRow(sku, 'quantity', quantity);
        },
        onShippingMethodSelected: function (data) {
            var shippingMethod = {
                name: data.name,
                cost: 0
            };
            this.setState({
                shippingMethod: shippingMethod
            });
        },
        onManualShippingCost: function (e) {
            var manualShippingCost = e.target.value;
            var shippingMethod = this.state.shippingMethod;
            shippingMethod.cost = manualShippingCost;
            this.setState({
                shippingMethod: shippingMethod
            });
        },
        onToggleDiscountBox: function (e) {
            var discount = this.state.discount;
            discount.active = (! discount.active);
            this.setState({
                discount: discount
            });
        },
        onDiscountValueUpdate: function (e) {
            var discount = this.state.discount;
            discount.value = e.target.value;
            this.setState({
                discount: discount
            });
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
                                  currency={this.props.currency}
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
            if (this.state.discount.active) {
                return (
                    <div className="discount-box">
                        <span className="discount-label">Discount</span>
                        <span className="discount-value">
                            <span className="currency-symbol">{this.props.currency.value}<input type="number" name="price" step="0.01" value={this.state.discount.value} onChange={this.onDiscountValueUpdate} /></span>
                        </span>
                        <span className="discount-actions">
                            <a onClick={this.onToggleDiscountBox}>Remove</a>
                        </span>
                    </div>
                );
            }

            return <a className="add-discount-action" onClick={this.onToggleDiscountBox}>Add Discount</a>
        },
        getSubtotalMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }
            var rowTotal = 0;
            this.state.orderRows.forEach(function (row) {
                rowTotal += parseFloat(row.price * row.quantity);
            });
            return (
                <div>
                    <span className="subtotal-label">Subtotal</span>
                    <span className="subtotal-value">{this.props.currency.value + " " + rowTotal.toFixed(2)}</span>
                </div>
            );
        },
        getShippingMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }

            return (
                <div>
                    <Select options={this.state.shippingOptions} onNewOption={this.onShippingMethodSelected} />
                    <span className="shipping-label">Shipping</span>
                    <span className="currency-symbol">{this.props.currency.value}<input type="number" name="price" step="0.01" value={this.state.shippingMethod.cost} onChange={this.onManualShippingCost} /></span>
                </div>
            );
        },
        getOrderTotalMarkup: function () {
            if (this.state.orderRows.length < 1) {
                return;
            }
            var orderTotal = parseFloat(this.state.shippingMethod.cost);

            if (this.state.discount.active) {
                orderTotal -= parseFloat(this.state.discount.value);
            }
            this.state.orderRows.forEach(function (row) {
                orderTotal += parseFloat(row.price * row.quantity);
            });
            return (
                <div>
                    <span className="total-label">Total</span>
                    <span className="total-value">{this.props.currency.value + " " + orderTotal.toFixed(2)}</span>
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