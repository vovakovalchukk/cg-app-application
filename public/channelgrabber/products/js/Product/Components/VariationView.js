define(['react'], function (React) {
    "use strict";

    var VariationViewComponent = React.createClass({
        displayName: 'VariationViewComponent',

        getAttributeHeaders: function () {
            var headers = [];
            this.props.attributeNames.forEach(function (attributeName) {
                var sortData = this.props.variationsSort.find(function (sort) {
                    return sort.attribute === attributeName;
                });
                headers.push(React.createElement(
                    'th',
                    { className: 'sortable', key: attributeName, onClick: this.props.onColumnSortClick.bind(this, attributeName) },
                    attributeName,
                    sortData ? React.createElement(
                        'span',
                        { className: 'sort-dir' },
                        sortData.ascending ? '▼' : '▲'
                    ) : ''
                ));
            }.bind(this));
            if (!headers.length) {
                headers.push(React.createElement('th', null));
            }
            return headers;
        },
        getAttributeValues: function (variation) {
            var values = [];
            this.props.attributeNames.forEach(function (attributeName) {
                values.push(React.createElement(
                    'td',
                    { key: attributeName },
                    variation.attributeValues[attributeName]
                ));
            });
            if (!values.length) {
                values.push(React.createElement('td', null));
            }
            return values;
        },
        getDefaultProps: function () {
            return {
                variations: [],
                attributeNames: [],
                fullView: false
            };
        },
        render: function () {
            var imageRow = 0;
            var variationRow = 0;
            return React.createElement(
                'div',
                { className: 'variation-table' },
                React.createElement(
                    'div',
                    { className: 'image-sku-table' },
                    React.createElement(
                        'table',
                        null,
                        React.createElement(
                            'thead',
                            null,
                            React.createElement(
                                'tr',
                                null,
                                React.createElement(
                                    'th',
                                    { key: 'image', className: 'image-col' },
                                    'Image'
                                ),
                                React.createElement(
                                    'th',
                                    { key: 'sky', className: 'sku-col' },
                                    'SKU'
                                )
                            )
                        ),
                        React.createElement(
                            'tbody',
                            null,
                            this.props.variations.map(function (variation) {
                                if (!this.props.fullView && imageRow > 1) {
                                    return;
                                }
                                imageRow++;
                                return React.createElement(
                                    'tr',
                                    { key: variation.id },
                                    React.createElement(
                                        'td',
                                        { key: 'image' },
                                        React.createElement('img', { src: variation.images.length > 0 ? variation.images[0]['url'] : this.context.imageBasePath + '/noproductsimage.png' })
                                    ),
                                    React.createElement(
                                        'td',
                                        { key: 'sku' },
                                        variation.sku
                                    )
                                );
                            }.bind(this))
                        )
                    )
                ),
                React.createElement(
                    'div',
                    { className: 'variations-table' },
                    React.createElement(
                        'table',
                        null,
                        React.createElement(
                            'thead',
                            null,
                            React.createElement(
                                'tr',
                                null,
                                this.getAttributeHeaders()
                            )
                        ),
                        React.createElement(
                            'tbody',
                            null,
                            this.props.variations.map(function (variation) {
                                if (!this.props.fullView && variationRow > 1) {
                                    return;
                                }
                                variationRow++;
                                return React.createElement(
                                    'tr',
                                    { key: variation.id },
                                    this.getAttributeValues(variation)
                                );
                            }.bind(this))
                        )
                    )
                )
            );
        }
    });

    VariationViewComponent.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return VariationViewComponent;
});
