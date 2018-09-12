define([
    'react',
    'Product/Filter/Entity',
    'Product/Components/Footer',
    'Product/Components/ProductRow',
    'Product/Components/ProductLinkEditor',
    'Product/Components/CreateListing/CreateListingRoot',
    'Product/Components/CreateProduct/CreateProductRoot',
    'Product/Storage/Ajax',
    'Product/Components/CreateListing/AccountSelectionRoot',
    'Product/Components/ProductList/Provider',
    'Product/Components/ProductList/Root',
    'Product/Components/CreateListing/ProductSearch/Root'
], function(
    React,
    ProductFilter,
    ProductFooter,
    ProductRow,
    ProductLinkEditor,
    CreateListingPopupRoot,
    CreateProductRoot,
    AjaxHandler,
    AccountSelectionRoot,
    ProductListProvider,
    ProductListRoot,
    ProductSearchRoot
) {
    "use strict";
    const NEW_PRODUCT_VIEW = 'NEW_PRODUCT_VIEW';
    const ACCOUNT_SELECTION_VIEW = 'ACCOUNT_SELECTION_VIEW';
    const NEW_LISTING_VIEW = 'NEW_LISTING_VIEW';
    const PRODUCT_LIST_VIEW = 'PRODUCT_LIST_VIEW';
    const PRODUCT_SEARCH_VIEW = 'PRODUCT_SEARCH_VIEW';
    
    var RootComponent = React.createClass({
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
            //todo remove these unusued listeners and test
            window.addEventListener('productDeleted', this.onDeleteProduct, false);
            window.addEventListener('productRefresh', this.onRefreshProduct, false);
            window.addEventListener('variationsRequest', this.onVariationsRequest, false);
            window.addEventListener('getProductsBySku', this.onSkuRequest, false);
        },
        componentWillUnmount: function() {
            this.productsRequest.abort();
            //todo remove these unusued listeners and test
            window.removeEventListener('productDeleted', this.onDeleteProduct, false);
            window.removeEventListener('productRefresh', this.onRefreshProduct, false);
            window.removeEventListener('variationsRequest', this.onVariationsRequest, false);
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
            let newCreateListing = Object.assign(this.state.createListing, {product},{});
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
            return <AccountSelectionRootComponent />;
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
        renderAddNewProductButton: function() {
            return (
                <div className=" navbar-strip--push-up-fix ">
                        <span className="navbar-strip__button " onClick={this.addNewProductButtonClick}>
                            <span className="fa-plus left icon icon--medium navbar-strip__button__icon">&nbsp;</span>
                            <span className="navbar-strip__button__text">Add</span>
                        </span>
                </div>
            )
        },
        renderProducts: function() {
            if (this.state.products.length === 0 && this.state.initialLoadOccurred) {
                return (
                    <div className="no-products-message-holder">
                        <span className="sprite-noproducts"></span>
                        <div className="message-holder">
                            <span className="heading-large">No Products to Display</span>
                            <span className="message">Please Search or Filter</span>
                        </div>
                    </div>
                );
            }
            return this.state.products.map(function(product) {
                return <ProductRow
                    key={product.id}
                    product={product}
                    variations={this.state.variations[product.id]}
                    productLinks={this.state.allProductLinks[product.id]}
                    maxVariationAttributes={this.state.maxVariationAttributes}
                    maxListingsPerAccount={this.state.maxListingsPerAccount}
                    linkedProductsEnabled={this.props.features.linkedProducts}
                    fetchingUpdatedStockLevelsForSkus={this.state.fetchingUpdatedStockLevelsForSkus}
                    accounts={this.state.accounts}
                    onCreateListingIconClick={this.onCreateListingIconClick.bind(this)}
                    createListingsAllowedChannels={this.state.createListingsAllowedChannels}
                    createListingsAllowedVariationChannels={this.state.createListingsAllowedVariationChannels}
                    adminCompanyUrl={this.props.adminCompanyUrl}
                    showVAT={this.props.showVAT}
                    massUnit={this.props.massUnit}
                    lengthUnit={this.props.lengthUnit}
                />;
            }.bind(this))
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
            var viewRenderers = this.getViewRenderers();
            var viewRenderer = viewRenderers[this.state.currentView];
            return viewRenderer();
        }
    });
    
    RootComponent.childContextTypes = {
        imageUtils: React.PropTypes.object,
        isAdmin: React.PropTypes.bool,
        initialVariationCount: React.PropTypes.number
    };
    
    return RootComponent;
});
