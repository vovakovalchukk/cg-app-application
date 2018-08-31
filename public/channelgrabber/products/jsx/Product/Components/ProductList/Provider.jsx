define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators/productActions',
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
        componentDidMount: function() {
            store.dispatch(ActionCreators.storeAccountFeatures(this.props.features));
            store.dispatch(ActionCreators.getProducts());
        },
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
