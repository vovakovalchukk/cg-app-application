define([
    'react',
    'Product/Components/StockRow'
], function(
    React,
    StockRow
) {
    "use strict";

    var StockViewComponent = React.createClass({
        getHeaders: function() {
            return [
                <th key="stock-available"><abbr title="Quantity of item available for sale">Available</abbr></th>,
                <th key="stock-undispatched"><abbr title="Quantity of item currently awaiting dispatch">Undispatched</abbr></th>,
                <th key="stock-total">Total</th>,
                <th key="stock-mode" colSpan="3" className="stock-mode-header-col">Stock Mode</th>
            ];
        },
        getDefaultProps: function() {
            return {
                variations: [],
                fullView: false
            };
        },
        totalUpdated: function(e) {
            var sku = e.type.substring('total-'.length);
            var newValue = e.detail.value;
            var updatedVariation = null;

            this.props.variations.forEach(function (variation) {
                if (variation.sku === sku) {
                    updatedVariation = variation;
                }
            });
            updatedVariation.stock.locations[0].onHand = newValue;
            this.props.onVariationDetailChanged(updatedVariation);
        },
        modeUpdated: function(e) {
            var sku = e.type.substring('mode-'.length);
            var stockMode = e.detail[sku];
            var updatedVariation = null;

            this.props.variations.forEach(function (variation) {
                if (variation.sku === sku) {
                    updatedVariation = variation;
                }
            });

            var stockModeOption = updatedVariation.stockModeOptions.find(function (option) {
                return option.value == stockMode.mode + "";
            });

            updatedVariation.stockModeDesc = stockModeOption.title;
            updatedVariation.stock.stockMode = stockMode.mode;
            updatedVariation.stock.stockLevel = stockMode.level || 0;

            this.props.onVariationDetailChanged(updatedVariation);
        },
        render: function () {
            var count = 0;
            return (
                <div className="stock-table">
                    <table>
                        <thead>
                        <tr>
                            {this.getHeaders()}
                        </tr>
                        </thead>
                        <tbody>
                        {this.props.variations.map(function (variation) {
                            if ((! this.props.fullView) && count > 1) {
                                return;
                            }
                            count++;
                            return <StockRow key={variation.id} variation={variation} totalUpdated={this.totalUpdated} modeUpdated={this.modeUpdated}/>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return StockViewComponent;
});
