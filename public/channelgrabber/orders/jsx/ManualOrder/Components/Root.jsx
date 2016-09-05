define([
    'react',
    'Product/Filter/Entity'
], function(
    React,
    ProductFilter
) {
    "use strict";

    var RootComponent = React.createClass({
        getChildContext: function() {
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
        performProductsRequest: function(searchTerm, pageNumber) {
            searchTerm = searchTerm || '';
            pageNumber = pageNumber || 1;

            var filter = new ProductFilter(searchTerm, null);
            filter.setPage(pageNumber);

            this.productsRequest = $.ajax({
                'url' : this.props.productsUrl,
                'data' : {'filter': filter.toObject()},
                'method' : 'POST',
                'dataType' : 'json',
                'success' : function(result) {
                    this.setState({
                        products: result.products
                    });
                }.bind(this),
                'error' : function () {
                    throw 'Unable to load products';
                }
            });
        },
        onPageChange: function(pageNumber) {
            this.performProductsRequest(null, pageNumber);
        },
        render: function()
        {
            return (
                <div>
                    {this.state.products.map(function (product) {
                        return <p>{product.sku}</p>
                    })}
                </div>
            );
        }
    });

    RootComponent.childContextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return RootComponent;
});