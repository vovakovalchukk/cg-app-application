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
                features: {}
            };
        },
        shouldComponentUpdate: function() {
            if (this.initialProductsShouldBeStored()) {
                store.dispatch(ActionCreators.initialSimpleAndParentProductsLoad(this.props.products))
            } else {
                return false;
            }
            return true;
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
});
