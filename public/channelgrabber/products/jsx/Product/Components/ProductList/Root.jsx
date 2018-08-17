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
        return {
            products: state.products,
            tabs: state.tabs
        };
    };
    
    const mapDispatchToProps = function(dispatch) {
        return {actions: Redux.bindActionCreators(ActionCreators, dispatch)};
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);
});
