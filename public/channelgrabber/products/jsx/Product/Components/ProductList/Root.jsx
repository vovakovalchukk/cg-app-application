define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators/productActions',
    'Product/Components/ProductList/ActionCreators/productLinkActions',
    'Product/Components/ProductList/Reducers/CombinedReducer',
    'Product/Components/ProductList/ProductList'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    actionCreators,
    productLinkActions,
    CombinedReducer,
    ProductList
) {
    "use strict";
    
    let combinedActionCreators = combineActionCreators();
    
    const mapStateToProps = function(state) {
        return {
            products: state.products,
            tabs: state.tabs,
            list: state.list
        };
    };
    
    const mapDispatchToProps = function(dispatch) {
        return {actions: Redux.bindActionCreators(combinedActionCreators, dispatch)};
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);

    function combineActionCreators(){
        return Object.assign(
            actionCreators,
            productLinkActions
        );
    }
});
