import React from 'react';
import CreateListingPopupRoot from 'Product/Components/CreateListing/CreateListingRoot'
import CreateProductRoot from 'Product/Components/CreateProduct/CreateProductRoot'
import AccountSelectionRoot from 'Product/Components/CreateListing/AccountSelectionRoot'
import ProductListProvider from 'Product/Components/ProductList/Provider'
import ProductSearchRoot from 'Product/Components/CreateListing/ProductSearch/Root'
"use strict";

const NEW_PRODUCT_VIEW = 'NEW_PRODUCT_VIEW';
const ACCOUNT_SELECTION_VIEW = 'ACCOUNT_SELECTION_VIEW';
const NEW_LISTING_VIEW = 'NEW_LISTING_VIEW';
const PRODUCT_LIST_VIEW = 'PRODUCT_LIST_VIEW';
const PRODUCT_SEARCH_VIEW = 'PRODUCT_SEARCH_VIEW';

let RootComponent = React.createClass({
    getChildContext: function() {
        return {
            imageUtils: this.props.utilities.image,
            isAdmin: this.props.isAdmin
        };
    },
    getDefaultProps: function() {
        return {
            searchAvailable: true,
            isAdmin: false,
            initialSearchTerm: '',
            adminCompanyUrl: null,
            managePackageUrl: null,
            features: {},
            taxRates: {},
            stockModeOptions: {},
            ebaySiteOptions: {},
            categoryTemplateOptions: {},
            createListingData: {},
            conditionOptions: {},
            defaultCurrency: null,
            salesPhoneNumber: null,
            demoLink: null,
            showVAT: true,
            massUnit: null,
            lengthUnit: null
        }
    },
    getInitialState: function() {
        return {
            currentView: PRODUCT_LIST_VIEW,
            maxVariationAttributes: 0,
            maxListingsPerAccount: [],
            initialLoadOccurred: false,
            accounts: {},
            createListing: {
                productId: null
            }
        }
    },
    componentDidMount: function() {
        window.addEventListener('getProductsBySku', this.onSkuRequest, false);
    },
    componentWillUnmount: function() {
        this.productsRequest.abort();
        window.removeEventListener('getProductsBySku', this.onSkuRequest, false);
    },
    filterBySearch: function(searchTerm) {
        this.performProductsRequest(null, searchTerm);
    },
    /**
     * @param skuList array
     */
    filterBySku: function(skuList) {
        this.performProductsRequest(null, null, skuList);
    },
    onCreateListingIconClick: function(createListingData) {
        let {
            product,
            createListingsAllowedChannels,
            createListingsAllowedVariationChannels,
            accounts,
            productSearchActive,
            variations
        } = createListingData;
        this.setState({
            currentView: ACCOUNT_SELECTION_VIEW,
            accounts,
            createListing: {
                product: product,
                variations,
                productSearchActive,
                createListingsAllowedChannels,
                createListingsAllowedVariationChannels
            }
        });
    },
    onCreateListingClose: function() {
        this.setState({
            currentView: PRODUCT_LIST_VIEW,
            createListing: {
                product: null
            }
        });
    },
    showAccountsSelectionPopup: function(product) {
        let newCreateListing = Object.assign(this.state.createListing, {product}, {});
        this.setState({
            currentView: ACCOUNT_SELECTION_VIEW,
            createListing: newCreateListing
        });
    },
    renderAccountSelectionPopup: function() {
        var AccountSelectionRootComponent = AccountSelectionRoot(
            this.state.accounts,
            this.state.createListing.createListingsAllowedChannels,
            this.state.createListing.createListingsAllowedVariationChannels,
            this.state.createListing.productSearchActive,
            this.onCreateListingClose,
            this.props.ebaySiteOptions,
            this.props.categoryTemplateOptions,
            this.showCreateListingPopup,
            this.showSearchPopup,
            this.state.createListing.product,
            this.props.listingCreationAllowed,
            this.props.managePackageUrl,
            this.props.salesPhoneNumber,
            this.props.demoLink
        );
        return <AccountSelectionRootComponent/>;
    },
    onSkuRequest: function(event) {
        this.filterBySku(event.detail.sku);
    },
    addNewProductButtonClick: function() {
        this.setState({
            currentView: NEW_PRODUCT_VIEW
        });
    },
    onCreateProductClose: function() {
        this.setState({
            currentView: PRODUCT_LIST_VIEW
        });
    },
    showCreateListingPopup: function(data) {
        this.setState({
            currentView: NEW_LISTING_VIEW,
            createListingData: data
        });
    },
    showSearchPopup: function(data) {
        this.setState({
            currentView: PRODUCT_SEARCH_VIEW,
            createListingData: data
        });
    },
    getViewRenderers: function() {
        return {
            NEW_PRODUCT_VIEW: this.renderCreateNewProduct,
            NEW_LISTING_VIEW: this.renderCreateListingPopup,
            PRODUCT_LIST_VIEW: this.renderProductListView,
            ACCOUNT_SELECTION_VIEW: this.renderAccountSelectionPopup,
            PRODUCT_SEARCH_VIEW: this.renderProductSearchView,
        }
    },
    renderCreateListingPopup: function() {
        var variationData = this.state.createListing.variations[this.state.createListingData.product.id]
            ? this.state.createListing.variations[this.state.createListingData.product.id]
            : [this.state.createListingData.product];
        
        return <CreateListingPopupRoot
            {...this.state.createListingData}
            conditionOptions={this.formatConditionOptions()}
            variationsDataForProduct={variationData}
            accountsData={this.state.accounts}
            defaultCurrency={this.props.defaultCurrency}
            onCreateListingClose={this.onCreateListingClose}
            onBackButtonPressed={this.showAccountsSelectionPopup}
            massUnit={this.props.massUnit}
            lengthUnit={this.props.lengthUnit}
        />;
    },
    formatConditionOptions: function() {
        var options = [];
        for (var value in this.props.conditionOptions) {
            options.push({
                name: this.props.conditionOptions[value],
                value: value
            });
        }
        return options;
    },
    redirectToProducts: function() {
        this.state.currentView = PRODUCT_LIST_VIEW;
        this.forceUpdate();
    },
    renderCreateNewProduct: function() {
        return <CreateProductRoot
            onCreateProductClose={this.onCreateProductClose}
            taxRates={this.props.taxRates}
            stockModeOptions={this.props.stockModeOptions}
            redirectToProducts={this.redirectToProducts}
            onSaveAndList={this.showAccountsSelectionPopup}
            showVAT={this.props.showVAT}
            massUnit={this.props.massUnit}
            lengthUnit={this.props.lengthUnit}
        />
    },
    renderProductListView: function() {
        return (
            <div>
                <ProductListProvider
                    features={this.props.features}
                    addNewProductButtonClick={this.addNewProductButtonClick}
                    onCreateNewListingIconClick={this.onCreateListingIconClick}
                    stockModeOptions={this.props.stockModeOptions}
                />
            </div>
        )
    },
    renderProductSearchView: function() {
        return <ProductSearchRoot
            createListingData={this.state.createListingData}
            renderCreateListingPopup={this.showCreateListingPopup}
            onCreateListingClose={this.onCreateListingClose}
            onBackButtonPressed={this.showAccountsSelectionPopup}
            defaultProductImage={this.props.utilities.image.getImageSource()}
        />;
    },
    render: function() {
        let viewRenderers = this.getViewRenderers();
        let viewRenderer = viewRenderers[this.state.currentView];
        return viewRenderer();
    }
});

RootComponent.childContextTypes = {
    imageUtils: React.PropTypes.object,
    isAdmin: React.PropTypes.bool,
    initialVariationCount: React.PropTypes.number
};

export default RootComponent;
