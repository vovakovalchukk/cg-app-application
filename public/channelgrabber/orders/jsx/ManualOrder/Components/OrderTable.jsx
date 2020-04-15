import PropTypes from 'prop-types';
import React from 'react';
import ItemRow from 'Common/Components/ItemRow';
import SearchBox from 'Common/Components/SearchBox';
import CurrencyInput from 'Common/Components/CurrencyInput';
import ProductFilter from 'Product/Filter/Entity';
import AjaxHandler from 'Product/Storage/Ajax';

class OrderTable extends React.Component {
    state = {
        shippingMethod: {
            name: "N/A",
            cost: 0
        },
        discount: {
            active: false,
            value: 0
        },
        orderRows: []
    };

    componentDidMount() {
        this.fetchProductsForOrderItems();
        this.setShippingDataOnState();
        window.addEventListener('productSelection', this.onProductSelected);
        window.addEventListener('orderSubmit', this.onOrderSubmit);
    }

    componentWillUnmount() {
        window.removeEventListener('productSelection', this.onProductSelected);
        window.removeEventListener('orderSubmit', this.onOrderSubmit);
    }

    fetchProductsForOrderItems = () => {
        const skus = this.props.orderItems.map((orderItem) => {
            return orderItem.sku;
        });

        if (skus.length === 0) {
            return;
        }

        this.fetchProductsBySkus(skus);
    };

    fetchProductsBySkus = (skus) => {
        if (skus.length === 0) {
            return;
        }

        n.notice(`Please wait while we populate the order items...`, true, 3000);

        let filter = new ProductFilter;
        filter.sku = skus;
        filter.limit = 500;
        filter.replaceVariationWithParent = true;
        filter.embedVariationsAsLinks = false;
        filter.embeddedDataToReturn = ['stock', 'variation', 'image'];
        AjaxHandler.fetchByFilter(filter, this.populateWithProducts);
    };

    populateWithProducts = (response) => {
        this.props.orderItems.forEach((orderItem) => {
            this.addItemRow(
                this.findProductForOrderItem(response.products, orderItem),
                orderItem.sku,
                orderItem.quantity,
                orderItem.price
            );
        });
    };

    findProductForOrderItem = (products, orderItem) => {
        const product = products.find((product) => {
            if (product.sku == orderItem.sku) {
                return true;
            }

            if (product.variationCount === 0 || product.variations.length === 0) {
                return false;
            }

            for (let variation of product.variations) {
                if (variation.sku == orderItem.sku) {
                    return true;
                }
            }
        });

        return product || {
            sku: orderItem.sku,
            name: orderItem.name
        };
    };

    setShippingDataOnState = () => {
        this.setState({
            shippingMethod: {
                name: this.props.shippingData.method || 'N/A',
                cost: this.props.shippingData.cost || 0
            }
        });
    };

    onProductSelected = (e) => {
        var data = e.detail;
        this.addItemRow(data.product, data.sku, data.quantity);
    };

    onOrderSubmit = (e) => {
        this.props.getOrderData(this.state);
    };

    addItemRow = (product, sku, quantity, price = 0) => {
        var orderRows = this.state.orderRows.slice();

        var alreadyAddedToForm = orderRows.find(function (row) {
            if (row.sku === sku) {
                row.quantity += parseInt(quantity);
                return true;
            }
        });
        if (! alreadyAddedToForm) {
            orderRows.push({product: product, sku: sku, quantity: quantity, price: price});
        }

        this.setState({
            orderRows: orderRows
        });
    };

    onRowRemove = (sku) => {
        var orderRows = this.state.orderRows.filter(function (row) {
            return row.sku !== sku;
        });
        this.setState({
            orderRows: orderRows
        });
    };

    onSkuChanged = (oldSku, selection) => {
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
    };

    onPriceChanged = (sku, price) => {
        this.updateItemRow(sku, 'price', price);
    };

    onStockQuantityUpdated = (sku, quantity) => {
        this.updateItemRow(sku, 'quantity', quantity);
    };

    onShippingMethodSelected = (methodName) => {
        var shippingMethod = this.state.shippingMethod;
        shippingMethod.name = methodName;
        this.setState({
            shippingMethod: shippingMethod
        });
    };

    onManualShippingCost = (e) => {
        var manualShippingCost = e.target.value;
        var shippingMethod = this.state.shippingMethod;
        shippingMethod.cost = manualShippingCost;
        this.setState({
            shippingMethod: shippingMethod
        });
    };

    onToggleDiscountBox = (e) => {
        var discount = this.state.discount;
        discount.active = (! discount.active);
        this.setState({
            discount: discount
        });
    };

    onDiscountValueUpdate = (e) => {
        var discount = this.state.discount;
        discount.value = e.target.value;
        this.setState({
            discount: discount
        });
    };

    updateItemRow = (sku, key, value) => {
        var orderRows = this.state.orderRows.slice();
        orderRows.forEach(function (row) {
            if (row.sku === sku) {
                row[key] = value;
            }
        });
        this.setState({
            orderRows: orderRows
        });
    };

    getItemRowsMarkup = () => {
        return (
            this.state.orderRows.map(function (row) {
                return (
                    <ItemRow
                        row={row}
                        currency={this.props.currency}
                        price={row.price}
                        onSkuChange={this.onSkuChanged}
                        onStockQuantityUpdate={this.onStockQuantityUpdated}
                        onPriceChange={this.onPriceChanged}
                        onRowRemove={this.onRowRemove}
                    />
                )
            }.bind(this))
        );
    };

    getDiscountMarkup = () => {
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
    };

    getSubtotalMarkup = () => {
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
    };

    getShippingMarkup = () => {
        return (
            <div className="detail-shipping">
                <span className="detail-label"><SearchBox placeholder="Shipping method..." results={this.context.carrierUtils.getCarriers()} onResultSelected={this.onShippingMethodSelected} />Shipping</span>
                <CurrencyInput value={this.state.shippingMethod.cost} currency={this.props.currency.value} onChange={this.onManualShippingCost}/>
            </div>
        );
    };

    getOrderTotalMarkup = () => {
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
    };

    getFloat = (stringNumber) => {
        var floatNumber = parseFloat(stringNumber);
        return isNaN(floatNumber) ? 0 : floatNumber;
    };

    render() {
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
}

OrderTable.contextTypes = {
    carrierUtils: PropTypes.object
};

export default OrderTable;
