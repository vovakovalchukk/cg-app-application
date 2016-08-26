define([
    'react',
    'Product/Components/Search',
    'Product/Filter/Entity',
    'Product/Components/List'
], function(
    React,
    SearchBox,
    ProductFilter,
    ProductList
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
                products: []
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
        performProductsRequest: function(searchTerm) {
            searchTerm = searchTerm || '';

            $('#products-loading-message').show();
            var filter = new ProductFilter(searchTerm, null);

            this.productsRequest = $.ajax({
                'url' : this.props.productsUrl,
                'data' : {'filter': filter.toObject()},
                'method' : 'POST',
                'dataType' : 'json',
                'success' : function(result) {
                    this.setState({
                        products: result.products
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
        render: function()
        {
            return (
                <div>
                    {this.getSearchBox()}
                    <ProductList products={this.state.products} />
                </div>
            );
        }
    });

    RootComponent.childContextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return RootComponent;
});