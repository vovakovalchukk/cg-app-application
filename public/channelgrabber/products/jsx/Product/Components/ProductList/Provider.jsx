define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators',
    'Product/Components/ProductList/Reducers/CombinedReducer',
    'Product/Components/ProductList/Root'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    ActionCreators,
    CombinedReducer,
    ProductListRoot
) {
    "use strict";
    
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
        componentWillReceiveProps: function(newProps) {
            // console.log('in componentWillReceiveProps newProps: '  ,newProps , 'typeof newProps.allProductsLinks: ', typeof newProps.allProductsLinks, ' isEmptyObject(newProps.allProductsLinks) : ' , isEmptyObject(newProps.allProductsLinks) );
            // console.log('typeof newProps.allProductsLinks : ' , typeof newProps.allProductsLinks);
            if (this.productLinksShouldBeStored(newProps.allProductsLinks)) {
                console.log('readytostore product links... in componentWIllReceiveProps with all ProductLinks newPRops: ', newProps);
                store.dispatch(ActionCreators.productsLinksLoad(newProps.allProductsLinks))
            }else{
                console.log('not sotring product links');
                
                
            }
        },
        shouldComponentUpdate: function() {
            if (this.initialProductsShouldBeStored()) {
                store.dispatch(ActionCreators.initialSimpleAndParentProductsLoad(this.props.products))
            } else {
                return false;
            }
            return true;
        },
        productLinksShouldBeStored: function(productLinks) {
            let storeState = store.getState();
            let productsLinksAreValid = typeof productLinks === 'object' && !isEmptyObject(productLinks)
            let productsLinksAreDifferentToThoseInState = storeState.products.allProductsLinks !== productLinks
            return productsLinksAreValid && productsLinksAreDifferentToThoseInState;
        },
        initialProductsShouldBeStored: function() {
            let storeState = store.getState();
            return this.props.products.length && !storeState.products.completeInitialLoads.simpleAndParentProducts;
        },
        render: function() {
            if (!this.props.products || !this.props.products.length) {
                return <span>no products available</span>
            }
            return (
                <Provider store={store}>
                    <ProductListRoot {...this.props} />
                </Provider>
            );
        }
    });
    
    return ProductListProvider;
    
    function isEmptyObject(obj) {
        return Object.getOwnPropertyNames(obj).length === 0;
    }
});
