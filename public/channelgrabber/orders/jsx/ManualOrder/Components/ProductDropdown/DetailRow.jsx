define([
    'react',
    'ManualOrder/Components/IncrementorInput'
], function(
    React,
    Input
) {
    "use strict";
    var DetailRow = React.createClass({
        getAttributeValues: function (attributes) {
            var attributeValues = [];
            for (var value in attributes) {
                if (!attributes.hasOwnProperty(value)) continue;
                attributeValues.push(<span className="variation-attribute">{value}</span>);
            }
            return attributeValues;
        },
        getVariationRow: function (variation) {
            return (
                <div className="detail-variation-row">
                    <div className="variation-row-img">
                        <img src={variation.images.length > 0 ? variation.images[0]['url'] : this.context.imageBasePath + '/noproductsimage.png'} />
                    </div>
                    <div className="variation-row-sku">
                        {variation.sku}
                    </div>
                    <div className="variation-row-attributes">
                        {this.getAttributeValues(variation.attributeValues)}
                    </div>
                    <div className="variation-row-stock">
                        {variation.stockLevel ? variation.stockLevel : 0} Available
                    </div>
                    <div className="variation-row-qty-input">
                        <Input name='quantity' initialValue={this.getQuantity(variation.sku)} submitCallback={this.onStockQuantitySelected.bind(this, variation.sku)} />
                    </div>
                    <div className="variation-row-actions">
                        <span className="variation-add-action" onClick={this.onAddClicked.bind(this, variation)}>Add</span>
                    </div>
                </div>
            );
        },
        getQuantity: function (sku) {
            return this.state.selectedQuantity[sku] ? this.state.selectedQuantity[sku] : this.defaultQuantityValue;
        },
        onAddClicked: function (variation) {
            var selectedQuantity = this.getQuantity(variation.sku);
            this.props.onAddClicked(variation, selectedQuantity);
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
                variations: []
            }
        },
        render: function () {
            return (
                <div className="detail-row-wrapper">
                    <div className="detail-row-header">
                        {this.props.name}
                    </div>
                    <div className="detail-row-variations-list">
                        {this.props.variations.map(function (variation) {
                            return this.getVariationRow(variation);
                        }.bind(this))}
                    </div>
                </div>
            );
        }
    });

    DetailRow.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return DetailRow;
});