define([
    'react',
    'Product/Components/Input',
    'Product/Components/Select'
], function(
    React,
    Input,
    Select
) {
    "use strict";
    var OrderRow = React.createClass({
        getInitialState: function () {
            return {
                price: 0
            }
        },
        getOptionComponents: function (attributes, variation) {
            var optionComponents = [];
            attributes.forEach(function (attributeName) {
                if (variation.attributeValues[attributeName] === undefined) {
                    optionComponents.push('');
                    return;
                }
                optionComponents.push(variation.attributeValues[attributeName]);
            });
            optionComponents.push("("+variation.stock.locations[0].onHand+")");
            return optionComponents;
        },
        getVariationSwitcherDropdown: function (product, thisSku) {
            if (! product.variations) {
                return;
            }
            var selectedOption = null;
            var options = product.variations.map(function (variation) {
                var optionName = this.getOptionComponents(product.attributeNames, variation);
                var option = {value: variation.sku, name: optionName};
                if (thisSku === variation.sku) {
                    selectedOption = option;
                }
                return {value: variation.sku, name: optionName};
            }.bind(this));
            return <Select options={options} onNewOption={this.props.onSkuChange.bind(this, thisSku)} selectedOption={selectedOption}/>
        },
        onPriceChange: function (e) {
            var price = e.target.value;
            if (price < 0) {
                price = 0;
            }
            this.setState({
                price: price
            });
            this.props.onPriceChange(this.props.row.sku, price);
        },
        onStockQuantityUpdate: function (e) {
            var quantity = e.target.value;
            this.props.onStockQuantityUpdate(this.props.row.product.sku, quantity);
        },
        render: function () {
            var currency = "£";
            return (
                <div className="order-row">
                    <div className="order-row-img">
                        <img src={this.context.manualOrderUtils.getProductImage(this.props.row.product, this.props.row.sku)} />
                    </div>
                    <div className="order-row-description">
                        <div className="order-row-name">{this.props.row.product.name}</div>
                        <div className="order-row-sku">{this.props.row.sku}</div>
                    </div>
                    <div className="order-row-attributes">
                        {this.getVariationSwitcherDropdown(this.props.row.product, this.props.row.sku)}
                    </div>
                    <div className="order-row-price">
                        <span className="currency-symbol">{currency}<input type="number" name="price" step="0.01" value={this.state.price} onChange={this.onPriceChange} /></span>
                    </div>
                    <div className="order-row-qty-input">
                        <span className="multiplier">x</span>
                        <Input name='quantity' initialValue={this.props.row.quantity} submitCallback={this.props.onStockQuantityUpdate} />
                    </div>
                    <div className="order-row-total">
                        {currency + (this.props.row.price * this.props.row.quantity).toFixed(2)}
                    </div>
                </div>
            );
        }
    });

    OrderRow.contextTypes = {
        manualOrderUtils: React.PropTypes.object
    };

    return OrderRow;
});