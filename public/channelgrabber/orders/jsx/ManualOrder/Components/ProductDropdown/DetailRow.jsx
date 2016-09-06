define([
    'react'
], function(
    React
) {
    "use strict";
    var Row = React.createClass({
        getVariationRow: function (variation) {
            return (
                <p>{variation.sku}</p>
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

    return Row;
});