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
    var OrderTable = React.createClass({
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
            return <Select options={options} onNewOption={this.props.onSkuChange} selectedOption={selectedOption}/>
        },
        render: function () {
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
                        <input type="number" name="price" value="0" onChange={this.props.onPriceChange} />
                    </div>
                    <div className="order-row-qty-input">
                        <span className="multiplier">X</span>
                        <Input name='quantity' initialValue={this.props.row.quantity} submitCallback={this.props.onStockQuantityUpdate.bind(this, this.props.row.product.sku)} />
                    </div>
                    <div className="order-row-total">
                        {this.props.row.price}
                    </div>
                </div>
            );
        }
    });

    OrderTable.contextTypes = {
        manualOrderUtils: React.PropTypes.object
    };

    return OrderTable;
});