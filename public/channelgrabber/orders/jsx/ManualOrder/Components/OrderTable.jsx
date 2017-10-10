define([
    'react',
    'Common/Components/ItemRow',
    'Common/Components/SearchBox',
    'Common/Components/CurrencyInput'
], function(
    React,
    ItemRow,
    SearchBox,
    CurrencyInput
) {
    "use strict";
    var OrderTable = React.createClass({
        getInitialState: function () {
            return {
                shippingMethod: {
                    name: "N/A",
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
            window.addEventListener('orderSubmit', this.onOrderSubmit);
        },
        componentWillUnmount: function () {
            window.removeEventListener('productSelection', this.onProductSelected);
            window.removeEventListener('orderSubmit', this.onOrderSubmit);
        },
        onProductSelected: function (e) {
            var data = e.detail;
            this.addItemRow(data.product, data.sku, data.quantity);
        },
        onOrderSubmit: function (e) {
            this.props.getOrderData(this.state);
        },
        addItemRow: function (product, sku, quantity) {
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
            var newSku = selection.value;
            if (selection === undefined || oldSku === newSku) {
                return;
            }

            var oldSkuQuantity = 0;
            var orderRows = this.state.orderRows.slice();
            orderRows.forEach(function (row) {
                if (row.sku === oldSku) {
                    oldSkuQuantity = row.quantity;
                }
            });

            var alreadyAddedToForm = orderRows.find(function (row) {
                if (row.sku === newSku) {
                    row.quantity += parseInt(oldSkuQuantity);
                    return true;
                }
            });
            if (alreadyAddedToForm) {
                this.onRowRemove(oldSku);
                return;
            }
            this.updateItemRow(oldSku, 'sku', selection.value);
        },
        onPriceChanged: function (sku, price) {
            this.updateItemRow(sku, 'price', price);
        },
        onStockQuantityUpdated: function (sku, quantity) {
            this.updateItemRow(sku, 'quantity', quantity);
        },
        onShippingMethodSelected: function (methodName) {
            var shippingMethod = this.state.shippingMethod;
            shippingMethod.name = methodName;
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
        updateItemRow: function (sku, key, value) {
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
        getItemRowsMarkup: function () {
            return (
                this.state.orderRows.map(function (row) {
                    return (
                        <ItemRow row={row}
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
            if (this.state.discount.active) {
                return (
                    <div className="discount-box">
                        <span className="detail-label">Discount</span>
                        <span className="detail-value">
                            <CurrencyInput value={this.state.discount.value} currency={this.props.currency.value} onChange={this.onDiscountValueUpdate}/>
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
            var subTotal = 0;
            if (this.state.discount.active) {
                subTotal -= this.getFloat(this.state.discount.value);
            }
            this.state.orderRows.forEach(function (row) {
                subTotal += this.getFloat(row.price * row.quantity);
            }.bind(this));
            return (
                <div>
                    <span className="bold detail-label">Subtotal</span>
                    <span className="subtotal-value">{this.props.currency.value + " " + subTotal.toFixed(2)}</span>
                </div>
            );
        },
        getShippingMarkup: function () {
            return (
                <div className="detail-shipping">
                    <span className="detail-label"><SearchBox placeholder="Shipping method..." results={this.context.carrierUtils.getCarriers()} onResultSelected={this.onShippingMethodSelected} />Shipping</span>
                    <CurrencyInput value={this.state.shippingMethod.cost} currency={this.props.currency.value} onChange={this.onManualShippingCost}/>
                </div>
            );
        },
        getOrderTotalMarkup: function () {
            var orderTotal = this.getFloat(this.state.shippingMethod.cost);

            if (this.state.discount.active) {
                orderTotal -= this.getFloat(this.state.discount.value);
            }
            this.state.orderRows.forEach(function (row) {
                orderTotal += this.getFloat(row.price * row.quantity);
            }.bind(this));
            return (
                <div>
                    <span className="bold detail-label">Total</span>
                    <span className="detail-value">{this.props.currency.value + " " + orderTotal.toFixed(2)}</span>
                </div>
            );
        },
        getFloat: function (stringNumber) {
            var floatNumber = parseFloat(stringNumber);
            return isNaN(floatNumber) ? 0 : floatNumber;
        },
        render: function () {
            return (
                <div className="order-table-wrapper">
                    <div className="order-rows-wrapper">{this.getItemRowsMarkup()}</div>
                    <div className="discount-wrapper">{this.getDiscountMarkup()}</div>
                    <div className="detail-wrapper">{this.getSubtotalMarkup()}</div>
                    <div className="detail-wrapper">{this.getShippingMarkup()}</div>
                    <div className="detail-wrapper">{this.getOrderTotalMarkup()}</div>
                </div>
            );
        }
    });

    OrderTable.contextTypes = {
        carrierUtils: React.PropTypes.object
    };

    return OrderTable;
});