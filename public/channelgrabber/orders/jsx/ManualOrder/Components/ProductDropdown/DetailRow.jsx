define([
    'react'
], function(
    React
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
                        {variation.stockLevel} Available
                    </div>
                    <div className="variation-row-qty-input">
                        <input value={variation.stockLevel} />
                    </div>
                    <div className="variation-row-actions">
                        <span className="variation-add-action">Add</span>
                    </div>
                </div>
            );
        },
        getDefaultProps: function () {
            return {
                product: {},
                variations: []
            }
        },
        render: function () {
            //console.log(this.props.variations);
            return (
                <div className="detail-row-wrapper">
                    <div className="detail-row-header">
                        {this.props.product.name}
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