define([
    'react',
    'Common/Components/ClickOutside',
    'Product/Filter/Entity',
    'ManualOrder/Components/ProductDropdown/DetailRow',
    'Product/Storage/Ajax'
], function(
    React,
    ClickOutside,
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
                var productsWithVariations = this.state.products.slice();
                productsWithVariations.forEach(function (product) {
                    if (variationsByParent[product.id]) {
                        product.variations = variationsByParent[product.id];
                    }
                });
                this.setState({
                    active: true,
                    fetchingData: false,
                    products: productsWithVariations
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
        onClickOutside: function (e) {
            this.setState({
                active: false
            });
        },
        onClick: function (e) {
            var newState = {
                active: !this.state.active
            };
            if (! this.state.active) {
                newState['searchTerm'] = '';
            }
            this.setState(newState);
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
        onOptionSelected: function (product, sku, quantity) {
            var data = {'quantity': quantity, 'sku': sku, 'product': product};
            var optionSelectedEvent = new CustomEvent('productSelection', {'detail': data});
            window.dispatchEvent(optionSelectedEvent);
        },
        getDropdown: function () {
            if (this.state.searchTerm.length < 1 && this.state.products.length < 1) {
                return;
            }
            return (
                <div className="detail-dropdown-popup">
                    <div className="dropdown-count">{this.state.products.length + (this.state.products.length === 1 ? ' product' : ' products')}</div>
                    {this.state.products.map(function (product) {
                        return <DetailRow product={product} onAddClicked={this.onOptionSelected}/>
                    }.bind(this))}
                </div>
            );
        },
        render: function () {
            return (
                <ClickOutside onClickOutside={this.onClickOutside}>
                    <div className={"detail-dropdown-wrapper "+ (this.state.active ? 'active' : '')} onClick={this.onClick}>
                        <div className="detail-dropdown-searchbox">
                            <div className="sprite-search-18-black"></div>
                            <input onChange={this.onChange} value={this.state.searchTerm} onKeyPress={this.onKeyPress}/>
                            <button className={"detail-search-btn button "+(this.state.fetchingData ? 'disabled' : '')} onClick={this.submitInput}>{this.state.fetchingData ? 'Fetching...' : 'Search'}</button>
                        </div>
                        {this.getDropdown()}
                    </div>
                </ClickOutside>
            );
        }
    });

    return ProductDropdown;
});