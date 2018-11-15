import PropTypes from 'prop-types';
import React from 'react';
import SearchBox from 'Product/Components/Search';
import ProductFilter from 'Product/Filter/Entity';
import ProductFooter from 'Product/Components/Footer';
import ProductRow from 'Product/Components/ProductRow';
import ProductLinkEditor from 'Product/Components/ProductLinkEditor';
import CreateListingPopupRoot from 'Product/Components/CreateListing/CreateListingRoot';
import CreateProductRoot from 'Product/Components/CreateProduct/CreateProductRoot';
import AjaxHandler from 'Product/Storage/Ajax';
import CreateListingRoot from 'Product/Components/CreateListing/Root';

const INITIAL_VARIATION_COUNT = 2;
const MAX_VARIATION_ATTRIBUTE_COLUMNS = 3;
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
        lengthUnit: null
    };

    state = {
        currentView: PRODUCT_LIST_VIEW,
        products: [],
        variations: [],
        allProductLinks: [],
        editingProductLink: {
            sku: "",
            links: []
        },
        maxVariationAttributes: 0,
        maxListingsPerAccount: [],
        initialLoadOccurred: false,
        pagination: {
            total: 0,
            limit: 0,
            page: 0
        },
        fetchingUpdatedStockLevelsForSkus: {},
        accounts: {},
        createListing: {
            productId: null
        }
    };

    getChildContext() {
        return {
            imageUtils: this.props.utilities.image,
            isAdmin: this.props.isAdmin,
            initialVariationCount: INITIAL_VARIATION_COUNT
        };
    }

    componentDidMount() {
        this.performProductsRequest();
        window.addEventListener('productDeleted', this.onDeleteProduct, false);
        window.addEventListener('productRefresh', this.onRefreshProduct, false);
        window.addEventListener('variationsRequest', this.onVariationsRequest, false);
        window.addEventListener('getProductsBySku', this.onSkuRequest, false);
        window.addEventListener('productLinkEditClicked', this.onEditProductLink, false);
        window.addEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
    }

    componentWillUnmount() {
        this.productsRequest.abort();
        window.removeEventListener('productDeleted', this.onDeleteProduct, false);
        window.removeEventListener('productRefresh', this.onRefreshProduct, false);
        window.removeEventListener('variationsRequest', this.onVariationsRequest, false);
        window.removeEventListener('getProductsBySku', this.onSkuRequest, false);
        window.removeEventListener('productLinkEditClicked', this.onEditProductLink, false);
        window.removeEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
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

    performProductsRequest = (pageNumber, searchTerm, skuList) => {
        pageNumber = pageNumber || 1;
        searchTerm = searchTerm || '';
        skuList = skuList || [];
        $('#products-loading-message').show();
        var filter = new ProductFilter(searchTerm, null, null, skuList);
        filter.setPage(pageNumber);
        function successCallback(result) {
            var self = this;
            this.setState({
                products: result.products,
                maxListingsPerAccount: result.maxListingsPerAccount,
                pagination: result.pagination,
                initialLoadOccurred: true,
                searchTerm: searchTerm,
                skuList: skuList,
                accounts: result.accounts,
                createListingsAllowedChannels: result.createListingsAllowedChannels,
                createListingsAllowedVariationChannels: result.createListingsAllowedVariationChannels,
                productSearchActive: result.productSearchActive,
                productSearchActiveForVariations: result.productSearchActiveForVariations
            }, function() {
                $('#products-loading-message').hide();
                self.onNewProductsReceived();
            });
        }
        function errorCallback() {
            throw 'Unable to load products';
        }
        this.fetchProducts(filter, successCallback, errorCallback);
    };

    fetchProducts = (filter, successCallback, errorCallback) => {
        this.productsRequest = $.ajax({
            'url': this.props.productsUrl,
            'data': {'filter': filter.toObject()},
            'method': 'POST',
            'dataType': 'json',
            'success': successCallback.bind(this),
            'error': errorCallback.bind(this)
        });
    };

    fetchVariations = (filter) => {
        $('#products-loading-message').show();
        function onSuccess(data) {
            var variationsByParent = this.sortVariationsByParentId(data.products, filter.getParentProductId());
            this.setState({
                variations: variationsByParent
            }, function() {
                this.fetchLinkedProducts();
                $('#products-loading-message').hide()
            }.bind(this));
        }
        AjaxHandler.fetchByFilter(filter, onSuccess.bind(this));
    };

    fetchLinkedProducts = () => {
        if (!this.props.features.linkedProducts) {
            return;
        }
        window.triggerEvent('fetchingProductLinksStart');
        var skusToFindLinkedProductsFor = {};
        for (var productId in this.state.variations) {
            this.state.variations[productId].forEach(function(variation) {
                skusToFindLinkedProductsFor[variation.sku] = variation.sku;
            });
        }
        this.state.products.forEach(function(product) {
            if (product.variationCount == 0 && product.sku) {
                skusToFindLinkedProductsFor[product.sku] = product.sku;
            }
        });
        $.ajax({
            url: '/products/links/ajax',
            data: {
                skus: JSON.stringify(skusToFindLinkedProductsFor)
            },
            type: 'POST',
            success: function(response) {
                var products = [];
                if (response.productLinks) {
                    products = response.productLinks;
                }
                this.setState({
                        allProductLinks: products
                    },
                    window.triggerEvent('fetchingProductLinksStop')
                );
            }.bind(this),
            error: function(error) {
                console.warn(error);
            }
        });
    };

    fetchUpdatedStockLevels = (productSku) => {
        var fetchingStockLevelsForSkuState = this.state.fetchingUpdatedStockLevelsForSkus;
        fetchingStockLevelsForSkuState[productSku] = true;
        var updateStockLevelsRequest = function() {
            $.ajax({
                url: '/products/stock/ajax/' + productSku,
                type: 'GET',
                success: function(response) {
                    var newState = this.state;
                    newState.products.forEach(function(product) {
                        if (product.variationCount == 0) {
                            if (!response.stock[product.sku]) {
                                return;
                            }
                            product.stock = response.stock[product.sku];
                            return;
                        }
                        newState.variations[product.id].forEach(function(product) {
                            if (!response.stock[product.sku]) {
                                return;
                            }
                            product.stock = response.stock[product.sku];
                            return;
                        });
                    });
                    newState.fetchingUpdatedStockLevelsForSkus[productSku] = false;
                    this.setState(newState);
                }.bind(this),
                error: function(error) {
                    console.error(error);
                }
            });
        }.bind(this);
        this.setState(
            fetchingStockLevelsForSkuState,
            updateStockLevelsRequest
        );
    };

    sortVariationsByParentId = (newVariations, parentProductId) => {
        var variationsByParent = {};
        if (parentProductId) {
            variationsByParent = this.state.variations;
            variationsByParent[parentProductId] = newVariations;
            return variationsByParent;
        }
        for (var index in newVariations) {
            var variation = newVariations[index];
            if (!variationsByParent[variation.parentProductId]) {
                variationsByParent[variation.parentProductId] = [];
            }
            variationsByParent[variation.parentProductId].push(variation);
        }
        return variationsByParent;
    };

    onProductLinkRefresh = () => {
        this.fetchLinkedProducts();
    };

    onEditProductLink = (event) => {
        var productSku = event.detail.sku;
        var productLinks = event.detail.productLinks;
        this.setState({
            editingProductLink: {
                sku: productSku,
                links: productLinks
            }
        });
    };

    onCreateListingIconClick = (productId) => {
        var product = this.state.products.find(function(product) {
            return product.id == productId;
        });
        this.showAccountsSelectionPopup(product);
    };

    showAccountsSelectionPopup = (product) => {
        this.setState({
            currentView: ACCOUNT_SELECTION_VIEW,
            createListing: {
                product: product
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

    onSkuRequest = (event) => {
        this.filterBySku(event.detail.sku);
    };

    onVariationsRequest = (event) => {
        var filter = new ProductFilter(null, event.detail.productId);
        this.fetchVariations(filter);
    };

    onDeleteProduct = (event) => {
        var deletedProductIds = event.detail.productIds;
        var products = this.state.products;
        var productsAfterDelete = [];
        products.forEach(function(product) {
            if (deletedProductIds.indexOf(product.id) < 0) {
                productsAfterDelete.push(product);
            }
        });
        this.setState({
            products: productsAfterDelete
        });
    };

    onRefreshProduct = (event) => {
        var refreshedProduct = event.detail.product;
        var refreshedProductId = (refreshedProduct.parentProductId === 0 ? refreshedProduct.id : refreshedProduct.parentProductId);
        var products = this.state.products.map(function(product) {
            if (product.id === refreshedProductId) {
                for (var listingId in product.listings) {
                    if (product.listings[listingId]) {
                        product.listings[listingId].status = 'pending';
                    }
                }
            }
            return product;
        });
        this.setState({
            products: products
        });
    };

    onNewProductsReceived = () => {
        var maxVariationAttributes = 1;
        var allDefaultVariationIds = [];
        this.state.products.forEach(function(product) {
            if (product.attributeNames.length > maxVariationAttributes) {
                maxVariationAttributes = product.attributeNames.length;
            }
            var defaultVariationIds = product.variationIds.slice(0, INITIAL_VARIATION_COUNT);
            allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
        });
        if (maxVariationAttributes > MAX_VARIATION_ATTRIBUTE_COLUMNS) {
            maxVariationAttributes = MAX_VARIATION_ATTRIBUTE_COLUMNS;
        }
        this.setState({maxVariationAttributes: maxVariationAttributes});
        if (allDefaultVariationIds.length == 0) {
            this.fetchLinkedProducts();
            return;
        }
        var productFilter = new ProductFilter(null, null, allDefaultVariationIds);
        this.fetchVariations(productFilter);
    };

    onPageChange = (pageNumber) => {
        this.performProductsRequest(pageNumber, this.state.searchTerm, this.state.skuList);
    };

    onProductLinksEditorClose = () => {
        this.setState({
            editingProductLink: {
                sku: "",
                links: []
            }
        });
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

    renderSearchBox = () => {
        if (this.props.searchAvailable) {
            return <SearchBox initialSearchTerm={this.props.initialSearchTerm}
                              submitCallback={this.filterBySearch}/>
        }
    };

    renderAddNewProductButton = () => {
        return (
            <div className=" navbar-strip--push-up-fix ">
                    <span className="navbar-strip__button " onClick={this.addNewProductButtonClick}>
                        <span className="fa-plus left icon icon--medium navbar-strip__button__icon">&nbsp;</span>
                        <span className="navbar-strip__button__text">Add</span>
                    </span>
            </div>
        )
    };

    renderProducts = () => {
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
    };

    renderAccountSelectionPopup = () => {
        var CreateListingRootComponent = CreateListingRoot(
            this.state.accounts,
            this.state.createListingsAllowedChannels,
            this.state.createListingsAllowedVariationChannels,
            this.state.productSearchActive,
            this.state.productSearchActiveForVariations,
            this.onCreateListingClose,
            this.props.ebaySiteOptions,
            this.props.categoryTemplateOptions,
            this.showCreateListingPopup,
            () => {},
            this.state.createListing.product,
            this.props.listingCreationAllowed,
            this.props.managePackageUrl,
            this.props.salesPhoneNumber,
            this.props.demoLink
        );
        this.fetchVariationForProductListingCreation();
        return <CreateListingRootComponent/>;
    };

    fetchVariationForProductListingCreation = () => {
        if (this.state.variations[this.state.createListing.product.id]
            && this.state.createListing.product.variationCount > this.state.variations[this.state.createListing.product.id].length
        ) {
            this.onVariationsRequest({detail: {productId: this.state.createListing.product.id}}, false);
        }
    };

    renderCreateListingPopup = () => {
        var variationData = this.state.variations[this.state.createListingData.product.id]
            ? this.state.variations[this.state.createListingData.product.id]
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
            <div id='products-app'>
                {this.renderSearchBox()}
                {this.props.features.createProducts ? this.renderAddNewProductButton() : ''}

                <div className='products-list__container'>
                    <div id="products-list">
                        {this.renderProducts()}
                    </div>
                    <ProductLinkEditor
                        productLink={this.state.editingProductLink}
                        onEditorClose={this.onProductLinksEditorClose}
                        fetchUpdatedStockLevels={this.fetchUpdatedStockLevels}
                    />
                    {(this.state.products.length ?
                        <ProductFooter pagination={this.state.pagination} onPageChange={this.onPageChange}/> : '')}
                </div>
            </div>
        );
    };

    render() {
        var viewRenderers = this.getViewRenderers();
        var viewRenderer = viewRenderers[this.state.currentView];
        return viewRenderer();
    }
}

RootComponent.childContextTypes = {
    imageUtils: PropTypes.object,
    isAdmin: PropTypes.bool,
    initialVariationCount: PropTypes.number
};

export default RootComponent;
