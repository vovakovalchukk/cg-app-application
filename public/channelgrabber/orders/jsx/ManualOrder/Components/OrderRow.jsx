define([
    'react',
    'Common/Components/Input',
    'Common/Components/Select'
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
            if (variation.stock === undefined) {
                return;
            }
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
            var sku = this.props.row.sku;
            this.props.onStockQuantityUpdate(sku, quantity);
        },
        onRowRemove: function (e) {
            this.props.onRowRemove(this.props.row.sku);
        },
        render: function () {
            return (
                <div className="order-row">
                    <div className="order-row-details">
                        <div className="order-row-img">
                            <img src={this.context.imageUtils.getProductImage(this.props.row.product, this.props.row.sku)} />
                        </div>
                        <div className="order-row-description">
                            <div className="order-row-name">{this.props.row.product.name}</div>
                            <div className="order-row-sku">{this.props.row.sku}</div>
                        </div>
                        <div className="order-row-attributes">
                            {this.getVariationSwitcherDropdown(this.props.row.product, this.props.row.sku)}
                        </div>
                        <div className="order-row-price">
                            <span className="currency-symbol">{this.props.currency.value}<input type="number" name="price" value={this.state.price} onChange={this.onPriceChange} /></span>
                        </div>
                        <div className="order-row-qty-input">
                            <span className="multiplier">x</span>
                            <input type="number" name='quantity' value={this.props.row.quantity} onChange={this.onStockQuantityUpdate} />
                        </div>
                    </div>
                    <div className="order-row-total">
                        {this.props.currency.value + " " + (this.props.row.price * this.props.row.quantity).toFixed(2)}
                    </div>
                    <div className="order-row-actions">
                        <a className="action remove" onClick={this.onRowRemove}>Remove</a>
                    </div>
                </div>
            );
        }
    });

    OrderRow.contextTypes = {
        imageUtils: React.PropTypes.object
    };

    return OrderRow;
});