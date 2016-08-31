define(['react', 'Product/Components/Search', 'Product/Filter/Entity', 'Product/Components/List'], function (React, SearchBox, ProductFilter, ProductList) {
    "use strict";

    var RootComponent = React.createClass({
        displayName: 'RootComponent',

        getChildContext() {
            return {
                imageBasePath: this.props.imageBasePath };
        },
        getInitialState: function () {
            return {
                products: []
            };
        },
        componentDidMount: function () {
            this.performProductsRequest();
        },
        componentWillUnmount: function () {
            this.productsRequest.abort();
        },
        filterBySearch: function (searchTerm) {
            this.performProductsRequest(searchTerm);
        },
        performProductsRequest: function (searchTerm) {
            searchTerm = searchTerm || '';

            $('#products-loading-message').show();
            var filter = new ProductFilter(searchTerm, null);

            this.productsRequest = $.ajax({
                'url': this.props.productsUrl,
                'data': { 'filter': filter.toObject() },
                'method': 'POST',
                'dataType': 'json',
                'success': function (result) {
                    this.setState({
                        products: result.products
                    });
                    $('#products-loading-message').hide();
                }.bind(this),
                'error': function () {
                    throw 'Unable to load products';
                }
            });
        },
        getSearchBox: function () {
            if (this.props.searchAvailable) {
                return React.createElement(SearchBox, { submitCallback: this.filterBySearch });
            }
        },
        render: function () {
            return React.createElement(
                'div',
                null,
                this.getSearchBox(),
                React.createElement(ProductList, { products: this.state.products })
            );
        }
    });

    RootComponent.childContextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return RootComponent;
});
