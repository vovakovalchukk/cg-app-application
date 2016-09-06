define([
    'react',
    'Product/Filter/Entity',
    'ManualOrder/Components/ProductDropdown/DetailRow',
    'Product/Storage/Ajax'
], function(
    React,
    ProductFilter,
    DetailRow,
    AjaxHandler
) {
    "use strict";
    var ProductDropdown = React.createClass({
        performProductsRequest: function(filter) {
            function products(data) {
                var allVariationIds = [];
                data.products.forEach(function(product) {
                    allVariationIds = allVariationIds.concat(product.variationIds);
                });
                this.setState({
                    products: data.products,
                });
                if (allVariationIds.length == 0) {
                    this.setState({
                        active: true,
                        fetchingData: false
                    });
                    return;
                }
                var variationFilter = new ProductFilter(null, null, allVariationIds);
                this.performVariationsRequest(variationFilter);
            }
            AjaxHandler.fetchByFilter(filter, products.bind(this));
        },
        performVariationsRequest: function(filter) {
            function variations(data) {
                var variationsByParent = {};
                for (var index in data.products) {
                    var variation = data.products[index];
                    if (!variationsByParent[variation.parentProductId]) {
                        variationsByParent[variation.parentProductId] = [];
                    }
                    variationsByParent[variation.parentProductId].push(variation);
                }
                this.setState({
                    active: true,
                    fetchingData: false,
                    variations: variationsByParent
                });
            }
            AjaxHandler.fetchByFilter(filter, variations.bind(this));
        },
        submitInput: function () {
            if (this.state.fetchingData) {
                return;
            }
            this.setState({
                fetchingData: true
            });
            var filter = new ProductFilter(this.state.searchTerm);
            this.performProductsRequest(filter);
        },
        getInitialState: function () {
            return {
                active: false,
                fetchingData: false,
                searchTerm: '',
                products: [],
                variations: [],
            }
        },
        onClick: function (e) {
            this.setState({
                active: !this.state.active
            });
        },
        onChange: function (e) {
            this.setState({
              searchTerm: e.target.value
            });
        },
        onKeyPress: function (e) {
            if (e.key === 'Enter') {
                this.submitInput();
            }
        },
        getDropdown: function () {
            if (this.state.products.length < 1) {
                return;
            }
            return (
                <div className="detail-dropdown-popup">
                    <div className="dropdown-count">{this.state.products.length} products</div>
                    {this.state.products.map(function (product) {
                        return <DetailRow product={product} variations={this.state.variations[product.id]}/>
                    }.bind(this))}
                </div>
            );
        },
        render: function () {
            return (
                <div className={"detail-dropdown-wrapper "+ (this.state.active ? 'active' : '')} onClick={this.onClick}>
                    <div className="detail-dropdown-searchbox">
                        <span className="icon-search"></span>
                        <input onChange={this.onChange} value={this.state.searchTerm} onKeyPress={this.onKeyPress}/>
                        <button className={"detail-search-btn "+(this.state.fetchingData ? 'disabled' : '')} onClick={this.submitInput}>{this.state.fetchingData ? 'Fetching...' : 'Search'}</button>
                    </div>
                    {this.getDropdown()}
                </div>
            );
        }
    });

    return ProductDropdown;
});