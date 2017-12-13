define([
    'react',
    'Common/Components/ClickOutside',
    'Product/Filter/Entity',
    'Common/Components/ProductDropdown/DetailRow',
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
        getInitialState: function () {
            return {
                hasFocus: false,
                fetchingData: false,
                searchTerm: '',
                products: [],
                variations: [],
                showResults: false
            }
        },
        getDefaultProps: function() {
            return {
                skuThatProductsCantLinkFrom: null
            }
        },
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
                        showResults: true,
                        hasFocus: true,
                        fetchingData: false
                    });
                    return;
                }
                var variationFilter = new ProductFilter(null, null, allVariationIds, null, filter.skuThatProductsCantLinkFrom);
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
                    showResults: true,
                    hasFocus: true,
                    fetchingData: false,
                    products: productsWithVariations
                });
            }
            AjaxHandler.fetchByFilter(filter, variations.bind(this));
        },
        submitInput: function () {
            if (this.props.disabled || this.state.fetchingData) {
                return;
            }
            this.setState({
                fetchingData: true
            });
            var filter = new ProductFilter(
                this.state.searchTerm,
                null,
                null,
                null,
                this.props.skuThatProductsCantLinkFrom
            );

            this.performProductsRequest(filter);
        },
        onClickOutside: function (e) {
            this.setState({
                hasFocus: false
            });
        },
        onClick: function (e) {
            var newState = {
                hasFocus: !this.state.hasFocus
            };
            if (! this.state.hasFocus) {
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
            window.triggerEvent('productSelection', data);
        },
        getDropdown: function () {
            var productsList = null;

            if (this.state.products.length) {
                productsList = this.state.products.map(function (product) {
                    return <DetailRow product={product} onAddClicked={this.onOptionSelected}/>
                }.bind(this));
            }
            return (
                <div className="detail-dropdown-popup">
                    <div className="dropdown-count">{this.state.products.length + (this.state.products.length === 1 ? ' product' : ' products')}</div>
                    {productsList}
                </div>
            );
        },
        render: function () {
            return (
                <ClickOutside onClickOutside={this.onClickOutside}>
                    <div className={"detail-dropdown-wrapper "+ (this.state.hasFocus && this.state.showResults && (! this.state.fetchingData) ? 'active' : '')}>
                        <div className="detail-dropdown-searchbox">
                            <div className="sprite-search-18-black"></div>
                            <input disabled={this.props.disabled} onChange={this.onChange} value={this.state.searchTerm} onKeyPress={this.onKeyPress} onClick={this.onClick}/>
                            <button className={"detail-search-btn button "+((this.state.fetchingData || this.props.disabled) ? 'disabled' : '')} onClick={this.submitInput}>{this.state.fetchingData ? 'Fetching...' : 'Search'}</button>
                        </div>
                        {this.getDropdown()}
                    </div>
                </ClickOutside>
            );
        }
    });

    return ProductDropdown;
});