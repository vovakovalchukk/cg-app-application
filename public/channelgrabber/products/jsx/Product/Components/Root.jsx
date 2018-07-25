// import 'fixed-data-table-2/dist/fixed-data-table.css';


define([
    'react',
    'fixed-data-table',
    'Product/Components/Search',
    'Product/Filter/Entity',
    'Product/Components/Footer',
    'Product/Components/ProductRow',
    'Product/Components/ProductLinkEditor',
    'Product/Components/CreateListing/CreateListingRoot',
    'Product/Components/CreateProduct/CreateProductRoot',
    'Product/Storage/Ajax',
    'Product/Components/CreateListing/Root',
], function(
    React,
    FixedDataTable,
    SearchBox,
    ProductFilter,
    ProductFooter,
    ProductRow,
    ProductLinkEditor,
    CreateListingPopupRoot,
    CreateProductRoot,
    AjaxHandler,
    CreateListingRoot
) {
    "use strict";
    const INITIAL_VARIATION_COUNT = 2;
    const MAX_VARIATION_ATTRIBUTE_COLUMNS = 3;
    const NEW_PRODUCT_VIEW = 'NEW_PRODUCT_VIEW';
    const ACCOUNT_SELECTION_VIEW = 'ACCOUNT_SELECTION_VIEW';
    const NEW_LISTING_VIEW = 'NEW_LISTING_VIEW';
    const PRODUCT_LIST_VIEW = 'PRODUCT_LIST_VIEW';

    const Cell = FixedDataTable.Cell;
    const Table = FixedDataTable.Table;
    const Column = FixedDataTable.Column;
    
    let TextCell = React.createClass({
        render:function(){
            console.log('in text cell render class data: ' , data, ' field ', field);
            let data = this.props.data;
            let field = this.props.field;
            let rowIndex = this.props.rowIndex;
            return (
                <Cell>
                    in cell
                    {data[rowIndex][field]}
                </Cell>
            )
        }
    });
    //
    var RootComponent = React.createClass({
        getChildContext: function() {
            return {
                imageUtils: this.props.utilities.image,
                isAdmin: this.props.isAdmin,
                initialVariationCount: INITIAL_VARIATION_COUNT
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
            }
        },
        componentDidMount: function() {
            this.performProductsRequest();
            window.addEventListener('productDeleted', this.onDeleteProduct, false);
            window.addEventListener('productRefresh', this.onRefreshProduct, false);
            window.addEventListener('variationsRequest', this.onVariationsRequest, false);
            window.addEventListener('getProductsBySku', this.onSkuRequest, false);
            window.addEventListener('productLinkEditClicked', this.onEditProductLink, false);
            window.addEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
        },
        componentWillUnmount: function() {
            this.productsRequest.abort();
            window.removeEventListener('productDeleted', this.onDeleteProduct, false);
            window.removeEventListener('productRefresh', this.onRefreshProduct, false);
            window.removeEventListener('variationsRequest', this.onVariationsRequest, false);
            window.removeEventListener('getProductsBySku', this.onSkuRequest, false);
            window.removeEventListener('productLinkEditClicked', this.onEditProductLink, false);
            window.removeEventListener('productLinkRefresh', this.onProductLinkRefresh, false);
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
        performProductsRequest: function(pageNumber, searchTerm, skuList) {
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
                    createListingsAllowedVariationChannels: result.createListingsAllowedVariationChannels
                }, function() {
                    $('#products-loading-message').hide();
                    self.onNewProductsReceived();
                });
            }
            function errorCallback() {
                throw 'Unable to load products';
            }
            this.fetchProducts(filter, successCallback, errorCallback);
        },
        fetchProducts: function(filter, successCallback, errorCallback) {
            this.productsRequest = $.ajax({
                'url': this.props.productsUrl,
                'data': {'filter': filter.toObject()},
                'method': 'POST',
                'dataType': 'json',
                'success': successCallback.bind(this),
                'error': errorCallback.bind(this)
            });
        },
        fetchVariations: function(filter) {
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
        },
        fetchLinkedProducts: function() {
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
        },
        fetchUpdatedStockLevels(productSku) {
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
        },
        sortVariationsByParentId: function(newVariations, parentProductId) {
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
        },
        onProductLinkRefresh: function() {
            this.fetchLinkedProducts();
        },
        onEditProductLink: function(event) {
            var productSku = event.detail.sku;
            var productLinks = event.detail.productLinks;
            this.setState({
                editingProductLink: {
                    sku: productSku,
                    links: productLinks
                }
            });
        },
        onCreateListingIconClick: function(productId) {
            var product = this.state.products.find(function(product) {
                return product.id == productId;
            });
            this.showAccountsSelectionPopup(product);
        },
        showAccountsSelectionPopup: function(product) {
            this.setState({
                currentView: ACCOUNT_SELECTION_VIEW,
                createListing: {
                    product: product
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
        onSkuRequest: function(event) {
            this.filterBySku(event.detail.sku);
        },
        onVariationsRequest: function(event) {
            var filter = new ProductFilter(null, event.detail.productId);
            this.fetchVariations(filter);
        },
        onDeleteProduct: function(event) {
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
        },
        onRefreshProduct: function(event) {
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
        },
        onNewProductsReceived: function() {
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
        },
        onPageChange: function(pageNumber) {
            this.performProductsRequest(pageNumber, this.state.searchTerm, this.state.skuList);
        },
        onProductLinksEditorClose: function() {
            this.setState({
                editingProductLink: {
                    sku: "",
                    links: []
                }
            });
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
        getViewRenderers: function() {
            return {
                NEW_PRODUCT_VIEW: this.renderCreateNewProduct,
                NEW_LISTING_VIEW: this.renderCreateListingPopup,
                PRODUCT_LIST_VIEW: this.renderProductListView,
                ACCOUNT_SELECTION_VIEW: this.renderAccountSelectionPopup
            }
        },
        renderSearchBox: function() {
            if (this.props.searchAvailable) {
                return <SearchBox initialSearchTerm={this.props.initialSearchTerm}
                                  submitCallback={this.filterBySearch}/>
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
        
        getList: function(){
            let rowCount = 50;
            //todo - replace this dummy data with something significant
            return Array(rowCount).fill().map((val, i) => {
                return {
                    id: i,
                    image: 'http://via.placeholder.com/40',
                    link: 'https://app.dev.orderhub.io/products',
                    sku:'sku '+i,
                    name: 'Product Name '+i,
                    available:0,
                    text: 'lorem  sdfoisjdofnsigndigfdifgberineorgn'
                }
            });
        },
        
        renderAccountSelectionPopup: function() {
            var CreateListingRootComponent = CreateListingRoot(
                this.state.accounts,
                this.state.createListingsAllowedChannels,
                this.state.createListingsAllowedVariationChannels,
                this.onCreateListingClose,
                this.props.ebaySiteOptions,
                this.props.categoryTemplateOptions,
                this.showCreateListingPopup,
                this.state.createListing.product,
                this.props.listingCreationAllowed,
                this.props.managePackageUrl,
                this.props.salesPhoneNumber,
                this.props.demoLink
            );
            this.fetchVariationForProductListingCreation();
            return <CreateListingRootComponent/>;
        },
        fetchVariationForProductListingCreation: function() {
            if (this.state.variations[this.state.createListing.product.id]
                && this.state.createListing.product.variationCount > this.state.variations[this.state.createListing.product.id].length
            ) {
                this.onVariationsRequest({detail: {productId: this.state.createListing.product.id}}, false);
            }
        },
        renderCreateListingPopup: function() {
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
            />;
        },
        renderProducts: function() {
            // if (this.state.products.length === 0 && this.state.initialLoadOccurred) {
            //     return (
            //         <div className="no-products-message-holder">
            //             <span className="sprite-noproducts"></span>
            //             <div className="message-holder">
            //                 <span className="heading-large">No Products to Display</span>
            //                 <span className="message">Please Search or Filter</span>
            //             </div>
            //         </div>
            //     );
            // }
            let rows = this.getList();
            // return {
            //     id: i,
            //     image: 'http://via.placeholder.com/40',
            //     link: 'https://app.dev.orderhub.io/products',
            //     sku:'sku '+i,
            //     name: 'Product Name '+i,
            //     available:0,
            //     text: 'lorem  sdfoisjdofnsigndigfdifgberineorgn'
            // }
            return (
                <Table
                    rowHeight={50}
                    rowsCount={rows.length}
                    width={1000}
                    height={400}
                    headerHeight={50}
                    data={rows}
                    rowGetter={(index)=>{
                        return rows[index];
                    }}
                    footerHeight={0}
                    groupHeaderHeight={0}
                    showScrollbarX={true}
                    showScrollbarY={true}
                >
                    <Column
                        columnKey="id"
                        width={300}
                        label="id"
                        header={<Cell> id head </Cell>}
                        cell={ props => {
                            return(
                                <Cell>
                                    {props.rowIndex}
                                </Cell>
                            );
                        }}
                    />
                    <Column
                        columnKey="sku"
                        width={300}
                        label="sku"
                        header={<Cell> sku head </Cell>}
                        cell={ props => {
                            return(
                                <Cell>
                                    {rows[props.rowIndex][props.columnKey]}
                                </Cell>
                            );
                        }}
                    />
                </Table>
            )
        
        
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