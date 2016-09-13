define([
    'react',
    'ManualOrder/Components/IncrementorInput'
], function(
    React,
    Input
) {
    "use strict";
    var DetailRow = React.createClass({
        getAttributeValues: function (variation) {
            var attributeValues = [];
            this.props.product.attributeNames.forEach(function(attributeName) {
                var attributeValue = variation.attributeValues[attributeName];
                attributeValues.push(<span className="variation-attribute">{attributeValue === undefined ? "" : attributeValue}</span>);
            });
            return attributeValues;
        },
        getVariationRow: function (variation) {
            return (
                <div className="detail-variation-row">
                    <div className="variation-row-img">
                        <img src={this.context.manualOrderUtils.getProductImage(this.props.product, variation.sku)} />
                    </div>
                    <div className="variation-row-sku">
                        {variation.sku}
                    </div>
                    <div className="variation-row-attributes">
                        {this.getAttributeValues(variation)}
                    </div>
                    <div className="variation-row-stock">
                        {variation.stockLevel ? variation.stockLevel : 0} Available
                    </div>
                    <div className="variation-row-qty-input">
                        <Input name='quantity' initialValue={this.getQuantity(variation.sku)} submitCallback={this.onStockQuantitySelected.bind(this, variation.sku)} />
                    </div>
                    <div className="variation-row-actions">
                        <span className="variation-add-action" onClick={this.onAddClicked.bind(this, this.props.product, variation.sku)}>Add</span>
                    </div>
                </div>
            );
        },
        getQuantity: function (sku) {
            return this.state.selectedQuantity[sku] ? this.state.selectedQuantity[sku] : this.defaultQuantityValue;
        },
        onAddClicked: function (product, sku) {
            var selectedQuantity = this.getQuantity(sku);
            this.props.onAddClicked(product, sku, selectedQuantity);
        },
        onStockQuantitySelected: function (sku, quantity) {
            var newSelectedQuantities = this.state.selectedQuantity;
            newSelectedQuantities[sku] = quantity;

            this.setState({
                selectedQuantity: newSelectedQuantities
            });
            return new Promise(function(resolve) {
                resolve({savedValue: quantity});
            });
        },
        getInitialState: function () {
            this.defaultQuantityValue = 1;
            return {
                selectedQuantity: {}
            }
        },
        getDefaultProps: function () {
            return {
                product: {}
            }
        },
        render: function () {
            var variations = this.props.product.variations ? this.props.product.variations : [this.props.product];
            return (
                <div className="detail-row-wrapper">
                    <div className="detail-row-header">
                        {this.props.product.name}
                    </div>
                    <div className="detail-row-variations-list">
                        {variations.map(function (variation) {
                            return this.getVariationRow(variation);
                        }.bind(this))}
                    </div>
                </div>
            );
        }
    });

    DetailRow.contextTypes = {
        manualOrderUtils: React.PropTypes.object
    };

    return DetailRow;
});