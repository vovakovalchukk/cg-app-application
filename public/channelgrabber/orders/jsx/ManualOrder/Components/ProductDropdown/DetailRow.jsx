define([
    'react',
    'Product/Components/Input'
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
                        <Input name='quantity' initialValue="1" submitCallback={this.onStockQuantitySelected} />
                    </div>
                    <div className="variation-row-actions">
                        <span className="variation-add-action" onClick={this.onAddClicked.bind(this, variation)}>Add</span>
                    </div>
                </div>
            );
        },
        onAddClicked: function (variation) {
            this.props.onAddClicked(variation, this.state.selectedQuantity);
        },
        onStockQuantitySelected: function (name, quantity) {
            this.setState({selectedQuantity: quantity});
            return new Promise(function(resolve) {
                resolve({savedValue: quantity});
            });
        },
        getInitialState: function () {
            return {
                selectedQuantity: 1
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