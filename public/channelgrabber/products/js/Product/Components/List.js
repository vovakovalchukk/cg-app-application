define(['react', 'Product/Components/ProductRow', 'Product/Filter/Entity', 'Product/Storage/Ajax'], function (React, ProductRow, ProductFilter, AjaxHandler) {
    "use strict";

    var ListComponent = React.createClass({
        displayName: 'ListComponent',

        getInitialState: function () {
            return {
                variations: []
            };
        },
        fetchVariations: function (filter) {
            function variations(data) {
                var variationsByParent = {};
                for (var index in data.products) {
                    var variation = data.products[index];
                    if (!variationsByParent[variation.parentProductId]) {
                        variationsByParent[variation.parentProductId] = [];
                    }
                    variationsByParent[variation.parentProductId].push(variation);
                }
                this.setState({ variations: variationsByParent });
            }
            AjaxHandler.fetchByFilter(filter, variations.bind(this));
        },
        componentWillReceiveProps: function (nextProps) {
            var allDefaultVariationIds = [];
            nextProps.products.forEach(function (product) {
                var defaultVariationIds = product.variationIds.slice(0, 2);
                allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
            });

            if (allDefaultVariationIds.length == 0) {
                return;
            }

            var productFilter = new ProductFilter(null, null, allDefaultVariationIds);
            this.fetchVariations(productFilter);
            this.initialRequest = false;
        },
        componentDidMount: function () {
            this.initialRequest = true;
        },
        componentWillUnmount: function () {
            this.variationsRequest.abort();
        },
        getProducts: function () {
            if (this.props.products.length === 0 && this.initialRequest !== undefined) {
                return React.createElement(
                    'div',
                    { className: 'no-products-message-holder' },
                    React.createElement('span', { className: 'sprite-noproducts' }),
                    React.createElement(
                        'div',
                        { className: 'message-holder' },
                        React.createElement(
                            'span',
                            { className: 'heading-large' },
                            'No Products to Display'
                        ),
                        React.createElement(
                            'span',
                            { className: 'message' },
                            'Please Search or Filter'
                        )
                    )
                );
            }

            return this.props.products.map(function (object) {
                return React.createElement(ProductRow, { key: object.id, product: object, variations: this.state.variations[object.id] });
            }.bind(this));
        },
        render: function () {
            return React.createElement(
                'div',
                { id: 'products-list' },
                this.getProducts()
            );
        }
    });

    return ListComponent;
});
