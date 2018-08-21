define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/actionCreators',
    'Product/Components/ProductList/Reducers/combinedReducer',
    'Product/Components/ProductList/Root',
    'Product/Filter/Entity'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    ActionCreators,
    CombinedReducer,
    ProductListRoot,
    ProductFilter,
) {
    "use strict";
    
    const INITIAL_VARIATION_COUNT = 2;
    
    
    var Provider = ReactRedux.Provider;
    
    var enhancer = Redux.applyMiddleware(thunk.default);
    if (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
        enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
            latency: 0,
            name: 'ProductsList'
        })(Redux.applyMiddleware(thunk.default));
    }
    var store = Redux.createStore(
        CombinedReducer,
        enhancer
    );
    
    
    
    
    var ProductListProvider = React.createClass({
        getDefaultProps: function() {
            return {
                products: [],
                features: {},
                allProductsLinks: {}
            };
        },
        getInitialState: function() {
            return {
                initialProductsSaved: {}
            }
        },
        componentDidMount: function(){
          console.log('in CDM of Provider');
            store.dispatch(ActionCreators.getProducts());
        },
        // componentWillReceiveProps: function(newProps) {
        //     // if (this.shouldProductLinksBeStored(newProps.allProductsLinks)) {
        //     //     store.dispatch(ActionCreators.productsLinksLoad(newProps.allProductsLinks))
        //     // }
        // },
        // shouldComponentUpdate: function() {
        //     // if (this.initialProductsShouldBeStored()) {
        //     //     store.dispatch(ActionCreators.initialSimpleAndParentProductsLoad(this.props.products))
        //     // } else {
        //     //     return false;
        //     // }
        //     // return true;
        // },
        //
        // performProductsRequest: function(pageNumber, searchTerm, skuList) {
        //     pageNumber = pageNumber || 1;
        //     searchTerm = searchTerm || '';
        //     skuList = skuList || [];
        //     $('#products-loading-message').show();
        //     var filter = new ProductFilter(searchTerm, null, null, skuList);
        //     filter.setPage(pageNumber);
        //
        //     console.log('Provider - about to fetch with filter: ' , filter);
        //
        //     // store.dispatch(ActionCreators.getProducts(pageNumber))
        //     this.fetchProducts(filter, successCallback, errorCallback);
        //
        //     function successCallback(result) {
        //         console.log('Provider -in successCallback of performProductsRequest');
        //         var self = this;
        //         this.setState({
        //             products: result.products,
        //             maxListingsPerAccount: result.maxListingsPerAccount,
        //             pagination: result.pagination,
        //             initialLoadOccurred: true,
        //             searchTerm: searchTerm,
        //             skuList: skuList,
        //             accounts: result.accounts,
        //             createListingsAllowedChannels: result.createListingsAllowedChannels,
        //             createListingsAllowedVariationChannels: result.createListingsAllowedVariationChannels,
        //             productSearchActive: result.productSearchActive
        //         }, function() {
        //             $('#products-loading-message').hide();
        //             self.onNewProductsReceived();
        //         });
        //     }
        //     function errorCallback(err) {
        //         console.log('in Provider error callback with err: ' , err);
        //
        //
        //         throw 'Unable to load products';
        //     }
        // },
    
        // fetchProducts: function(filter, successCallback, errorCallback) {
        //     this.productsRequest = $.ajax({
        //         'url': PRODUCTS_URL,
        //         'data': {'filter': filter.toObject()},
        //         'method': 'POST',
        //         'dataType': 'json',
        //         'success': successCallback.bind(this),
        //         'error': errorCallback.bind(this)
        //     });
        // },
    
        // onNewProductsReceived: function() {
        //     var allDefaultVariationIds = [];
        //     this.state.products.forEach(function(product) {
        //         var defaultVariationIds = product.variationIds.slice(0, INITIAL_VARIATION_COUNT);
        //         allDefaultVariationIds = allDefaultVariationIds.concat(defaultVariationIds);
        //     });
        //     if (allDefaultVariationIds.length == 0) {
        //         // TODO -implement below via Redux
        //         store.dispatch(ActionCreators.getLinkedProducts())
        //         return;
        //     }
        //     var productFilter = new ProductFilter(null, null, allDefaultVariationIds);
        //     //TODO - implement below via redux
        //     console.log('about to fetch variations');
        //     store.dispatch(ActionCreators.fetchVariations(productFilter))
        // },
        // fetchLinkedProducts: function() {
        //     if (!this.props.features.linkedProducts) {
        //         return;
        //     }
        //     window.triggerEvent('fetchingProductLinksStart');
        //     var skusToFindLinkedProductsFor = {};
        //     for (var productId in this.state.variations) {
        //         this.state.variations[productId].forEach(function(variation) {
        //             skusToFindLinkedProductsFor[variation.sku] = variation.sku;
        //         });
        //     }
        //     this.state.products.forEach(function(product) {
        //         if (product.variationCount == 0 && product.sku) {
        //             skusToFindLinkedProductsFor[product.sku] = product.sku;
        //         }
        //     });
        //     $.ajax({
        //         url: '/products/links/ajax',
        //         data: {
        //             skus: JSON.stringify(skusToFindLinkedProductsFor)
        //         },
        //         type: 'POST',
        //         success: function(response) {
        //             var products = [];
        //             if (response.productLinks) {
        //                 products = response.productLinks;
        //             }
        //
        //             this.setState({
        //                     allProductLinks: products
        //                 },
        //                 window.triggerEvent('fetchingProductLinksStop')
        //             );
        //         }.bind(this),
        //         error: function(error) {
        //             console.warn(error);
        //         }
        //     });
        // },
        
        // shouldProductLinksBeStored: function(productLinks) {
        //     let storeState = store.getState();
        //     let productsLinksAreValid = typeof productLinks === 'object' && !isEmptyObject(productLinks)
        //     let productsLinksAreDifferentToThoseInState = storeState.products.allProductsLinks !== productLinks
        //     return productsLinksAreValid && productsLinksAreDifferentToThoseInState;
        // },
        // initialProductsShouldBeStored: function() {
        //     let storeState = store.getState();
        //     return this.props.products.length && !storeState.products.completeInitialLoads.simpleAndParentProducts;
        // },
        render: function() {
            return (
                <Provider store={store}>
                    <ProductListRoot {...this.props} />
                </Provider>
            );
        }
    });
    
    return ProductListProvider;
    
});
