define([
    'react',
    'Product/Components/Search',
    'Product/Filter/Entity',
    'Product/Components/List',
    'Product/Components/Footer'
], function(
    React,
    SearchBox,
    ProductFilter,
    ProductList,
    ProductFooter
) {
    "use strict";

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
                searchTerm: this.props.initialSearchTerm,
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
            document.addEventListener('productDeleted', this.onDeleteProduct, false);
            document.addEventListener('productRefresh', this.onRefreshProduct, false);
        },
        componentWillUnmount: function()
        {
            this.productsRequest.abort();
            document.removeEventListener('productDeleted', this.onDeleteProduct, false);
            document.removeEventListener('productRefresh', this.onRefreshProduct, false);
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
                this.setState({
                    products: result.products,
                    pagination: result.pagination
                }, function(){$('#products-loading-message').hide()});
            }
            function errorCallback() {
                throw 'Unable to load products';
            }
            this.getProducts(filter, successCallback, errorCallback);
        },
        getSearchBox: function() {
            if (this.props.searchAvailable) {
                return <SearchBox initialSearchTerm={this.props.initialSearchTerm} submitCallback={this.filterBySearch}/>
            }
        },
        onPageChange: function(pageNumber) {
            this.performProductsRequest(pageNumber);
        },
        getProducts: function (filter, successCallback, errorCallback) {
            this.productsRequest = $.ajax({
                'url' : this.props.productsUrl,
                'data' : {'filter': filter.toObject()},
                'method' : 'POST',
                'dataType' : 'json',
                'success' : successCallback.bind(this),
                'error' : errorCallback.bind(this)
            });
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
        render: function()
        {
            return (
                <div>
                    {this.getSearchBox()}
                    <ProductList products={this.state.products} />
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