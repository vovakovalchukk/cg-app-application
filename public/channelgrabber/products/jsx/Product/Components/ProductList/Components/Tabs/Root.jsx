define([
    'react',
    'redux',
    'react-redux',
    'Product/Components/ProductList/ActionCreators',
    'Product/Components/ProductList/Components/Tabs/Tabs',
], function(
    React,
    Redux,
    ReactRedux,
    AllActionCreators,
    Tabs
) {
    "use strict";
    
    const {changeTab} = AllActionCreators;
    const ComponentSpecificActionCreators = {
        changeTab
    };
    
    const mapStateToProps = function(state) {
        return {
            tabs:state.tabs
        };
    };
    
    const mapDispatchToProps = function(dispatch) {
        return {
            actions: Redux.bindActionCreators(ComponentSpecificActionCreators, dispatch)
        };
    };
    
    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(Tabs);
});
