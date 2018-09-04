define([
    'react',
    'redux',
    'react-redux',
    'Product/Components/ProductList/ActionCreators/tabActions',
    'Product/Components/ProductList/Components/Tabs/Tabs',
], function(
    React,
    Redux,
    ReactRedux,
    tabActions,
    Tabs
) {
    "use strict";
    
    const mapStateToProps = function(state) {
        return {
            tabs: state.tabs
        };
    };
    
    const mapDispatchToProps = function(dispatch) {
        return {
            actions: Redux.bindActionCreators(tabActions, dispatch)
        };
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(Tabs);
});
