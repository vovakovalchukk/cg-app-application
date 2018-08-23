define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/actionCreators',
    'Product/Components/ProductList/Reducers/CombinedReducer',
    'Product/Components/ProductList/ProductList'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    actionCreators,
    CombinedReducer,
    ProductList
) {
    "use strict";
    
    const mapStateToProps = function(state) {
        return {
            products: state.products,
            tabs: state.tabs,
            list: state.list
        };
    };
    
    const mapDispatchToProps = function(dispatch) {
        return {actions: Redux.bindActionCreators(actionCreators, dispatch)};
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);
});
