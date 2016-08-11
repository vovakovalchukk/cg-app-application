define([
    'react',
    'Product/Components/List'
], function(
    React,
    ProductList
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function()
        {
            return {
                products: []
            }
        },
        componentDidMount: function()
        {
            $('#products-loading-message').show();
            this.productsRequest = $.get(this.props.productsUrl, function(result) {
                this.setState({
                    products: result.products
                });
                $('#products-loading-message').hide();
            }.bind(this));
        },
        componentWillUnmount: function()
        {
            this.productsRequest.abort();
        },
        render: function()
        {
            return <ProductList products={this.state.products} imageBasePath={this.props.imageBasePath} />;
        }
    });

    return RootComponent;
});