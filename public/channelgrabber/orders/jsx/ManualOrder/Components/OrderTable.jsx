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
        getDefaultProps: function () {
            return {
                orderRows: []
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
            return <Select options={options} onNewOption={this.onSkuChanged} selectedOption={selectedOption}/>
        },
        onSkuChanged: function () {

        },
        onStockQuantityUpdated: function (sku, quantity) {
            console.log(sku);
            console.log(quantity);
        },
        getOrderRows: function () {
            return (
                this.props.orderRows.map(function (row) {
                    return (
                        <div className="order-row">
                            <div className="order-row-img">
                                <img src={this.context.manualOrderUtils.getProductImage(row.product, row.sku)} />
                            </div>
                            <div className="order-row-description">
                                <div className="order-row-name">{row.product.name}</div>
                                <div className="order-row-sku">{row.sku}</div>
                            </div>
                            <div className="order-row-attributes">
                                {this.getVariationSwitcherDropdown(row.product, row.sku)}
                            </div>
                            <div className="order-row-qty-input">
                                <span className="multiplier">X</span>
                                <Input name='quantity' initialValue={row.quantity} submitCallback={this.onStockQuantityUpdated.bind(this, row.product.sku)} />
                            </div>
                        </div>
                    )
                }.bind(this))
            );
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

    OrderTable.contextTypes = {
        manualOrderUtils: React.PropTypes.object
    };

    return OrderTable;
});