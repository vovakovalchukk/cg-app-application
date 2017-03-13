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

    const MAX_VARIATION_ATTRIBUTE_COLUMNS = 3;

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
            if (! this.requireVariationRequest) {
                return;
            }
            var maxVariationAttributes = 1;
            var allDefaultVariationIds = [];
            nextProps.products.forEach(function(product) {
                if (product.attributeNames.length > maxVariationAttributes) {
                    maxVariationAttributes = product.attributeNames.length;
                }
                var defaultVariationIds = product.variationIds.slice(0, 2);
                allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
            });
            if (maxVariationAttributes > MAX_VARIATION_ATTRIBUTE_COLUMNS) {
                maxVariationAttributes = MAX_VARIATION_ATTRIBUTE_COLUMNS;
            }
            this.setState({maxVariationAttributes: maxVariationAttributes});

            if (allDefaultVariationIds.length == 0) {
                return;
            }

            var productFilter = new ProductFilter(null, null, allDefaultVariationIds);
            this.fetchVariations(productFilter);
            this.requireVariationRequest = false;
        },
        componentDidMount: function () {
            this.requireVariationRequest = true;
            window.addEventListener('productsReceived', this.onProductsReceived, false);
            window.addEventListener('variationsReceived', this.onVariationsReceived, false);
        },
        componentWillUnmount: function () {
            window.removeEventListener('productsReceived', this.onProductsReceived, false);
            window.removeEventListener('variationsReceived', this.onVariationsReceived, false);
        },
        onProductsReceived: function (event) {
            this.requireVariationRequest = true;
        },
        onVariationsReceived: function (event) {
            var variationsByProductId = this.state.variations;
            variationsByProductId[event.detail.productId] = event.detail.variations;

            this.setState({variations: variationsByProductId});
        },
        getProducts: function () {
            if ((this.props.products.length === 0) && (this.requireVariationRequest !== undefined)) {
                 return (
                     <div className="no-products-message-holder">
                         <span className="sprite-noproducts"></span>
                         <div className="message-holder">
                             <span className="heading-large">No Products to Display</span>
                             <span className="message">Please Search or Filter</span>
                         </div>
                     </div>
                 );
            }

            return this.props.products.map(function(object) {
                return <ProductRow key={object.id} product={object} variations={this.state.variations[object.id]} maxVariationAttributes={this.state.maxVariationAttributes}/>;
            }.bind(this))
        },
        render: function()
        {
            return (
                <div id="products-list">
                    {this.getProducts()}
                </div>
            );

        }
    });

    return ListComponent;
});