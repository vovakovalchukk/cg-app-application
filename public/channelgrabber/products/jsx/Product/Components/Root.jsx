define([
    'react',
    'Product/Components/Search',
    'Product/Filter/Entity',
    'Product/Components/List',
    'Product/Components/Footer',
    'Product/Components/ProductRow',
    'Product/Storage/Ajax'
], function(
    React,
    SearchBox,
    ProductFilter,
    ProductList,
    ProductFooter,
    ProductRow,
    AjaxHandler
) {
    "use strict";

    const MAX_VARIATION_ATTRIBUTE_COLUMNS = 3;

    var RootComponent = React.createClass({
        getChildContext: function() {
            return {
                imageBasePath: this.props.imageBasePath,
                isAdmin: this.props.isAdmin
            };
        },
        getDefaultProps: function () {
            return {
                searchAvailable: true,
                isAdmin: false,
                initialSearchTerm: ''
            }
        },
        getInitialState: function()
        {
            return {
                products: [],
                variations: [],
                searchTerm: this.props.initialSearchTerm,
                maxVariationAttributes: 0,
                initialLoadOccurred: false,
                pagination: {
                    total: 0,
                    limit: 0,
                    page: 0
                }
            }
        },
        componentDidMount: function()
        {
            this.performProductsRequest();
            window.addEventListener('productDeleted', this.onDeleteProduct, false);
            window.addEventListener('productRefresh', this.onRefreshProduct, false);
            window.addEventListener('variationsRequest', this.onVariationsRequest, false);
        },
        componentWillUnmount: function()
        {
            this.productsRequest.abort();
            window.removeEventListener('productDeleted', this.onDeleteProduct, false);
            window.removeEventListener('productRefresh', this.onRefreshProduct, false);
            window.removeEventListener('variationsRequest', this.onVariationsRequest, false);
        },
        filterBySearch: function(searchTerm) {
            this.setState({
                searchTerm: searchTerm
            },
                this.performProductsRequest
            );
        },
        performProductsRequest: function(pageNumber) {
            pageNumber = pageNumber || 1;

            $('#products-loading-message').show();
            var filter = new ProductFilter(this.state.searchTerm, null);
            filter.setPage(pageNumber);

            function successCallback(result) {
                var self = this;
                this.setState({
                    products: result.products,
                    pagination: result.pagination,
                    initialLoadOccurred: true,
                }, function(){
                    $('#products-loading-message').hide();
                    self.onNewProductsReceived();
                });
            }
            function errorCallback() {
                throw 'Unable to load products';
            }
            this.fetchProducts(filter, successCallback, errorCallback);
        },
        fetchProducts: function (filter, successCallback, errorCallback) {
            this.productsRequest = $.ajax({
                'url' : this.props.productsUrl,
                'data' : {'filter': filter.toObject()},
                'method' : 'POST',
                'dataType' : 'json',
                'success' : successCallback.bind(this),
                'error' : errorCallback.bind(this)
            });
        },
        fetchVariations: function (filter) {
            $('#products-loading-message').show();
            function onSuccess(data) {
                var variationsByParent = this.sortVariationsByParentId(data.products, filter.getParentProductId());
                this.setState({
                    variations: variationsByParent
                }, function() {
                    $('#products-loading-message').hide()
                });
            }
            AjaxHandler.fetchByFilter(filter, onSuccess.bind(this));
        },
        sortVariationsByParentId: function (newVariations, parentProductId) {
            var variationsByParent = {};

            if (parentProductId) {
                variationsByParent = this.state.variations;
                variationsByParent[parentProductId] = newVariations;
                return variationsByParent;
            }

            for (var index in newVariations) {
                var variation = newVariations[index];
                if (!variationsByParent[variation.parentProductId]) {
                    variationsByParent[variation.parentProductId] = [];
                }
                variationsByParent[variation.parentProductId].push(variation);
            }
            return variationsByParent;
        },
        onVariationsRequest: function (event) {
            var filter = new ProductFilter(null, event.detail.productId);
            this.fetchVariations(filter);
        },
        onDeleteProduct: function (event) {
            var deletedProductIds = event.detail.productIds;

            var products = this.state.products;
            var productsAfterDelete = [];
            products.forEach(function (product) {
                if (deletedProductIds.indexOf(product.id) < 0) {
                    productsAfterDelete.push(product);
                }
            });

            this.setState({
                products: productsAfterDelete
            });
        },
        onRefreshProduct: function (event) {
            var refreshedProduct = event.detail.product;
            var refreshedProductId = (refreshedProduct.parentProductId === 0 ? refreshedProduct.id : refreshedProduct.parentProductId);
            var products = this.state.products.map(function (product) {
                if (product.id === refreshedProductId) {
                    product.listings[0].status = 'pending';
                    return product;
                }
                return product;
            });

            this.setState({
                products: products
            });
        },
        onNewProductsReceived: function () {
            var maxVariationAttributes = 1;
            var allDefaultVariationIds = [];
            this.state.products.forEach(function(product) {
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
        },
        onPageChange: function(pageNumber) {
            this.performProductsRequest(pageNumber);
        },
        renderSearchBox: function() {
            if (this.props.searchAvailable) {
                return <SearchBox initialSearchTerm={this.props.initialSearchTerm} submitCallback={this.filterBySearch}/>
            }
        },
        renderProducts: function () {
            if (this.state.products.length === 0 && this.state.initialLoadOccurred) {
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

            return this.state.products.map(function(object) {
                return <ProductRow key={object.id} product={object} variations={this.state.variations[object.id]} maxVariationAttributes={this.state.maxVariationAttributes}/>;
            }.bind(this))
        },
        render: function()
        {
            return (
                <div>
                    {this.renderSearchBox()}
                    <div id="products-list">
                        {this.renderProducts()}
                    </div>
                    {(this.state.products.length ? <ProductFooter pagination={this.state.pagination} onPageChange={this.onPageChange}/> : '')}
                </div>
            );
        }
    });

    RootComponent.childContextTypes = {
        imageBasePath: React.PropTypes.string,
        isAdmin: React.PropTypes.bool
    };

    return RootComponent;
});