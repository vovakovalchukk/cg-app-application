define([
    'react',
    'Product/Components/ProductRow',
    'Product/Filter/Entity',
    'Product/Storage/Ajax'
], function(
    React,
    ProductRow,
    ProductFilter,
    AjaxHandler
) {
    "use strict";

    var ListComponent = React.createClass({
        getInitialState: function() {
            return {
                variations: []
            }
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
                this.setState({variations: variationsByParent});
            }
            AjaxHandler.fetchByFilter(filter, variations.bind(this));
        },
        componentWillReceiveProps: function (nextProps) {
            var allDefaultVariationIds = [];
            nextProps.products.forEach(function(product) {
                var defaultVariationIds = product.variationIds.slice(0, 2);
                allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
            });

            if (allDefaultVariationIds.length == 0) {
                return;
            }

            var productFilter = new ProductFilter(null, null, allDefaultVariationIds);
            this.fetchVariations(productFilter);
        },
        componentWillUnmount: function () {
            this.variationsRequest.abort();
        },
        render: function()
        {
            var imageBasePath = this.props.imageBasePath;
            return (
                <div id="products-list">
                    {this.props.products.map(function(object) {
                        return <ProductRow key={object.id} product={object} variations={this.state.variations[object.id]}/>;
                    }.bind(this))}
                </div>
            );

        }
    });

    return ListComponent;
});