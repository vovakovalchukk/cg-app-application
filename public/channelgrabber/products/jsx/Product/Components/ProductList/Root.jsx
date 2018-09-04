define([
    'react',
    'redux',
    'react-redux',
    'redux-thunk',
    'Product/Components/ProductList/ActionCreators/productActions',
    'Product/Components/ProductList/ActionCreators/productLinkActions',
    'Product/Components/ProductList/ActionCreators/paginationActions',
    'Product/Components/ProductList/ActionCreators/searchActions',
    'Product/Components/ProductList/ActionCreators/tabActions',
    'Product/Components/ProductList/ProductList'
], function(
    React,
    Redux,
    ReactRedux,
    thunk,
    productActions,
    productLinkActions,
    paginationActions,
    searchActions,
    tabActions,
    ProductList
) {
    "use strict";
    
    let combinedActionCreators = combineActionCreators();
    
    const mapStateToProps = function(state) {
        return {
            products: state.products,
            tabs: state.tabs,
            list: state.list,
            pagination: state.pagination
        };
    };
    
    const mapDispatchToProps = function(dispatch) {
        return {actions: Redux.bindActionCreators(combinedActionCreators, dispatch)};
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductList);
    
    function combineActionCreators() {
        return Object.assign(
            productActions,
            productLinkActions,
            paginationActions,
            searchActions,
            tabActions
        );
    }
});
