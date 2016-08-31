define(['react', 'Product/Components/StockRow'], function (React, StockRow) {
    "use strict";

    var StockViewComponent = React.createClass({
        displayName: 'StockViewComponent',

        getHeaders: function () {
            return [React.createElement(
                'th',
                { key: 'stock-available' },
                React.createElement(
                    'abbr',
                    { title: 'Quantity of item available for sale' },
                    'Available'
                )
            ), React.createElement(
                'th',
                { key: 'stock-undispatched' },
                React.createElement(
                    'abbr',
                    { title: 'Quantity of item currently awaiting dispatch' },
                    'Undispatched'
                )
            ), React.createElement(
                'th',
                { key: 'stock-total' },
                'Total'
            ), React.createElement(
                'th',
                { key: 'stock-mode' },
                'Mode'
            ), React.createElement(
                'th',
                { key: 'stock-level' },
                React.createElement(
                    'abbr',
                    { title: 'Quantity of items that will be listed, according to the currently chosen Stock Mode.' },
                    'Level'
                )
            )];
        },
        getDefaultProps: function () {
            return {
                variations: [],
                fullView: false
            };
        },
        totalUpdated: function (e) {
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
        levelUpdated: function (e) {
            var sku = e.type.substring('level-'.length);
            var newValue = e.detail.value;
            var updatedVariation = null;

            this.props.variations.forEach(function (variation) {
                if (variation.sku === sku) {
                    updatedVariation = variation;
                }
            });
            updatedVariation.stock.stockLevel = newValue;
            this.props.onVariationDetailChanged(updatedVariation);
        },
        modeUpdated: function (e) {
            var sku = e.type.substring('mode-'.length);
            var newValue = e.detail.value;
            var updatedVariation = null;

            this.props.variations.forEach(function (variation) {
                if (variation.sku === sku) {
                    updatedVariation = variation;
                }
            });
            updatedVariation.stock.stockMode = newValue;
            this.props.onVariationDetailChanged(updatedVariation);
        },
        render: function () {
            var count = 0;
            return React.createElement(
                'div',
                { className: 'stock-table' },
                React.createElement(
                    'table',
                    null,
                    React.createElement(
                        'thead',
                        null,
                        React.createElement(
                            'tr',
                            null,
                            this.getHeaders()
                        )
                    ),
                    React.createElement(
                        'tbody',
                        null,
                        this.props.variations.map(function (variation) {
                            if (!this.props.fullView && count > 1) {
                                return;
                            }
                            count++;
                            return React.createElement(StockRow, { key: variation.id, variation: variation, totalUpdated: this.totalUpdated, levelUpdated: this.levelUpdated, modeUpdated: this.modeUpdated });
                        }.bind(this))
                    )
                )
            );
        }
    });

    return StockViewComponent;
});
