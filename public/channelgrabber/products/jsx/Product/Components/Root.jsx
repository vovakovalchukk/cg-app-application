import PropTypes from 'prop-types';
import React from 'react';
import CreateListingPopupRoot from 'Product/Components/CreateListing/CreateListingRoot'
import CreateProductRoot from 'Product/Components/CreateProduct/CreateProductRoot'
import AccountSelectionRoot from 'Product/Components/CreateListing/AccountSelectionRoot'
import ProductListProvider from 'Product/Components/ProductList/Provider'

import LoadingSpinner from 'Common/Components/LoadingSpinner'

"use strict";

const NEW_PRODUCT_VIEW = 'NEW_PRODUCT_VIEW';
const ACCOUNT_SELECTION_VIEW = 'ACCOUNT_SELECTION_VIEW';
const NEW_LISTING_VIEW = 'NEW_LISTING_VIEW';
const PRODUCT_LIST_VIEW = 'PRODUCT_LIST_VIEW';

class RootComponent extends React.Component {
    static defaultProps = {
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
        lengthUnit: null,
        pickLocations: [],
        pickLocationValues: []
    };

    state = {
        currentView: PRODUCT_LIST_VIEW,
        maxVariationAttributes: 0,
        maxListingsPerAccount: [],
        initialLoadOccurred: false,
        accounts: {},
        createListing: {
            productId: null
        }
    };

    getChildContext() {
        return {
            imageUtils: this.props.utilities.image,
            isAdmin: this.props.isAdmin
        };
    }

    componentDidMount() {
        window.addEventListener('getProductsBySku', this.onSkuRequest, false);
    }

    componentWillUnmount() {
        this.productsRequest.abort();
        window.removeEventListener('getProductsBySku', this.onSkuRequest, false);
    }

    filterBySearch = (searchTerm) => {
        this.performProductsRequest(null, searchTerm);
    };

    /**
     * @param skuList array
     */
    filterBySku = (skuList) => {
        this.performProductsRequest(null, null, skuList);
    };

    onCreateListingIconClick = (createListingData) => {
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
    };

    onCreateListingClose = () => {
        this.setState({
            currentView: PRODUCT_LIST_VIEW,
            createListing: {
                product: null
            }
        });
    };

    showAccountsSelectionPopup = (product) => {
        let newCreateListing = Object.assign(this.state.createListing, {product}, {});

        this.setState({
            currentView: ACCOUNT_SELECTION_VIEW,
            createListing: newCreateListing
        });
    };

    renderAccountSelectionPopup = () => {
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
    };

    onSkuRequest = (event) => {
        this.filterBySku(event.detail.sku);
    };

    addNewProductButtonClick = () => {
        this.setState({
            currentView: NEW_PRODUCT_VIEW
        });
    };

    onCreateProductClose = () => {
        this.setState({
            currentView: PRODUCT_LIST_VIEW
        });
    };

    showCreateListingPopup = (data) => {
        this.setState({
            currentView: NEW_LISTING_VIEW,
            createListingData: data
        });
    };

    getViewRenderers = () => {
        return {
            NEW_PRODUCT_VIEW: this.renderCreateNewProduct,
            NEW_LISTING_VIEW: this.renderCreateListingPopup,
            PRODUCT_LIST_VIEW: this.renderProductListView,
            ACCOUNT_SELECTION_VIEW: this.renderAccountSelectionPopup
        }
    };

    renderCreateListingPopup = () => {
        var variationData = this.state.createListing.variations
            ? this.state.createListing.variations
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
            defaultProductImage={this.props.utilities.image.getImageSource()}
        />;
    };

    formatConditionOptions = () => {
        var options = [];
        for (var value in this.props.conditionOptions) {
            options.push({
                name: this.props.conditionOptions[value],
                value: value
            });
        }
        return options;
    };
    redirectToProducts = () => {
        this.state.currentView = PRODUCT_LIST_VIEW;
        this.forceUpdate();
    };

    renderCreateNewProduct = () => {
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
    };

    renderProductListView = () => {

        return (
            <div className={"move-down"}>
                <LoadingSpinner
                    loaderStyle={''}
                />
            </div>
        )
//        return (
//            <div>
//                <ProductListProvider
//                    features={this.props.features}
//                    addNewProductButtonClick={this.addNewProductButtonClick}
//                    onCreateNewListingIconClick={this.onCreateListingIconClick}
//                    stockModeOptions={this.props.stockModeOptions}
//                    massUnit={this.props.massUnit}
//                    lengthUnit={this.props.lengthUnit}
//                    vatRates={this.props.taxRates}
//                    pickLocations={this.props.pickLocations}
//                    pickLocationValues={this.props.pickLocationValues}
//                />
//            </div>
//        )
    };

    render() {
        let viewRenderers = this.getViewRenderers();
        let viewRenderer = viewRenderers[this.state.currentView];
        return viewRenderer();
    }
}

RootComponent.childContextTypes = {
    imageUtils: PropTypes.object,
    isAdmin: PropTypes.bool,
    initialVariationCount: PropTypes.number
};

export default RootComponent;