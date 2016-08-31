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
        getChildContext() {
            return {
                imageBasePath: this.props.imageBasePath};
        },
        getInitialState: function()
        {
            return {
                products: [],
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
        },
        componentWillUnmount: function()
        {
            this.productsRequest.abort();
        },
        filterBySearch: function(searchTerm) {
            this.performProductsRequest(searchTerm);
        },
        performProductsRequest: function(searchTerm, pageNumber) {
            searchTerm = searchTerm || '';
            pageNumber = pageNumber || 1;

            $('#products-loading-message').show();
            var filter = new ProductFilter(searchTerm, null);
            filter.setPage(pageNumber);

            this.productsRequest = $.ajax({
                'url' : this.props.productsUrl,
                'data' : {'filter': filter.toObject()},
                'method' : 'POST',
                'dataType' : 'json',
                'success' : function(result) {
                    this.setState({
                        products: result.products,
                        pagination: result.pagination
                    });
                    $('#products-loading-message').hide();
                }.bind(this),
                'error' : function () {
                    throw 'Unable to load products';
                }
            });
        },
        getSearchBox: function() {
            if (this.props.searchAvailable) {
                return <SearchBox submitCallback={this.filterBySearch}/>
            }
        },
        onPageChange: function(pageNumber) {
            this.performProductsRequest(null, pageNumber);
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
        imageBasePath: React.PropTypes.string
    };

    return RootComponent;
});