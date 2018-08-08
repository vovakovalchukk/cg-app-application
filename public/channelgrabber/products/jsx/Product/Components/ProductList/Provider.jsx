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
        shouldComponentUpdate:function(){
            if(this.initialProductsShouldBeStored()){
                // console.log('!!!!!storing initial data...');
                store.dispatch(ActionCreators.initialSimpleAndParentProductsLoad(this.props.products))
                return true;
            }else{
                return false;
            }
            return true;
        },
        initialProductsShouldBeStored:function(){
            let storeState = store.getState();
            // console.log('!storeState.initialLoadComplete: ', !storeState.products.initialLoadComplete);
            // console.log('this.props.products.length: ', this.props.products.length);
            
            return this.props.products.length && !storeState.products.completeInitialLoads.simpleAndParentProducts;
        },
        render: function() {
            // console.log('in render of Provider this.props: '  , this.props);
            if(!this.props.products || !this.props.products.length){
                // todo add message here for when the user has no products
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
