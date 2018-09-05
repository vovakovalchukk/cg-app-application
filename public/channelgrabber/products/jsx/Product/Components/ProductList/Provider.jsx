define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators/productActions',
    'Product/Components/ProductList/ActionCreators/columnActions',
    'Product/Components/ProductList/Reducers/combinedReducer',
    'Product/Components/ProductList/Root',
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    ActionCreators,
    columnActions,
    combinedReducer,
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
        combinedReducer,
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
        componentWillMount:function(){
            console.log('props passing from Provider to List- ' , this.props);
        },
        componentDidMount: async function() {
            store.dispatch(ActionCreators.storeAccountFeatures(this.props.features));
            let getProductsResponse = await store.dispatch(ActionCreators.getProducts());
            store.dispatch(columnActions.generateColumns(getProductsResponse.accounts))
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
