define(['react', 'Product/Components/DimensionsRow'], function (React, DimensionsRow) {
    "use strict";

    var DimensionsViewComponent = React.createClass({
        displayName: 'DimensionsViewComponent',

        getHeaders: function () {
            return [React.createElement(
                'th',
                { key: 'weight' },
                'Weight (kg)'
            ), React.createElement(
                'th',
                { key: 'height' },
                'Height (cm)'
            ), React.createElement(
                'th',
                { key: 'width' },
                'Width (cm)'
            ), React.createElement(
                'th',
                { key: 'length' },
                'Length (cm)'
            )];
        },
        getDefaultProps: function () {
            return {
                variations: [],
                fullView: false
            };
        },
        dimensionUpdated: function (e) {
            var sku = e.type.substring('dimension-'.length);
            var newValue = e.detail.value;
            var dimension = e.detail.dimension;
            var updatedVariation = null;

            this.props.variations.forEach(function (variation) {
                if (variation.sku === sku) {
                    updatedVariation = variation;
                }
            });
            updatedVariation.details[dimension] = newValue;
            this.props.onVariationDetailChanged(updatedVariation);
        },
        render: function () {
            var count = 0;
            return React.createElement(
                'div',
                { className: 'details-table' },
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
                            return React.createElement(DimensionsRow, { key: variation.id, variation: variation, dimensionUpdated: this.dimensionUpdated });
                        }.bind(this))
                    )
                )
            );
        }
    });

    return DimensionsViewComponent;
});
