define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput'
], function(
    React,
    Select,
    CurrencyInput
) {
    "use strict";
    var ItemRow = React.createClass({
        getInitialState: function () {
            return {
                price: 0
            }
        },
        getVariationSwitcherOptions: function (attributes, variation) {
            var optionComponents = [];
            attributes.forEach(function (attributeName) {
                if (variation.attributeValues[attributeName] === undefined) {
                    optionComponents.push('');
                    return;
                }
                optionComponents.push(variation.attributeValues[attributeName]);
            });

            if (variation.stock === undefined) {
                return optionComponents;
            }
            optionComponents.push("("+variation.stock.locations[0].onHand+")");
            return optionComponents;
        },
        getVariationSwitcherDropdown: function (product, thisSku) {
            if (! product.variations) {
                return;
            }
            var selectedOption = null;
            var options = product.variations.map(function (variation) {
                var optionName = this.getVariationSwitcherOptions(product.attributeNames, variation);
                var option = {value: variation.sku, name: optionName};
                if (thisSku === variation.sku) {
                    selectedOption = option;
                }
                return {value: variation.sku, name: optionName};
            }.bind(this));
            return <Select disabled={this.props.disabled} options={options} onOptionChange={this.props.onSkuChange.bind(this, thisSku)} selectedOption={selectedOption}/>
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
        renderPriceInput: function () {
            if (this.props.currency !== undefined) {
                return (
                    <div className="item-row-price">
                        <CurrencyInput value={this.state.price} currency={this.props.currency.value} onChange={this.onPriceChange}/>
                    </div>
                );
            }
        },
        renderPriceTotal: function () {
            if (this.props.currency !== undefined) {
                return (
                    <div className="item-row-total">
                        {this.props.currency.value + " " + (this.props.row.price * this.props.row.quantity).toFixed(2)}
                    </div>
                );
            }
        },
        render: function () {
            return (
                <div className="item-row">
                    {this.props.disabled ? <div className="disable-mask"></div> : ''}
                    <div className="item-row-details">
                        <div className="item-row-img">
                            <img src={this.context.imageUtils.getProductImage(this.props.row.product, this.props.row.sku)} />
                        </div>
                        <div className="item-row-description">
                            <div className="item-row-name" title={this.props.row.product.name}>{this.props.row.product.name}</div>
                            <div className="item-row-sku" title={this.props.row.sku}>{this.props.row.sku}</div>
                        </div>
                        <div className="item-row-attributes">
                            {this.getVariationSwitcherDropdown(this.props.row.product, this.props.row.sku)}
                        </div>
                        {this.renderPriceInput()}
                        <div className="item-row-qty-input">
                            <span className="multiplier">x</span>
                            <input disabled={this.props.disabled} type="number" name='quantity' placeholder="0.00" value={this.props.row.quantity ? this.props.row.quantity : ''} onChange={this.onStockQuantityUpdate} />
                        </div>
                    </div>
                    {this.renderPriceTotal()}
                    <div className="item-row-actions">
                        <a className="action remove" onClick={this.onRowRemove}>Remove</a>
                    </div>
                </div>
            );
        }
    });

    ItemRow.contextTypes = {
        imageUtils: React.PropTypes.object
    };

    return ItemRow;
});