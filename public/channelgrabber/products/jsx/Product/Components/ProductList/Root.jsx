define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators',
    'Product/Components/ProductList/Reducers/CombinedReducer',
    'Product/Components/ProductList/ProductList'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    ActionCreators,
    CombinedReducer,
    ProductList
) {
    "use strict";
    
    const mapStateToProps = function(state) {
        console.log('root - in productList mapStateToProps: ' , state);
        return {
            products: state.products
        };
    };
    
    
    // const mapDispatchToProps = function(dispatch) {
    //     return Redux.bindActionCreators(ActionCreators, dispatch);
    // };
    
    const mapDispatchToProps = function(dispatch) {
        return {actions:Redux.bindActionCreators(ActionCreators, dispatch)};
    };
    
    // return ProductListRoot;
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);
});
