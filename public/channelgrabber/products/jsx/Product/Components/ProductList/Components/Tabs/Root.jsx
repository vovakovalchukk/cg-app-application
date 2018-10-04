import React from 'react';
import Redux from 'redux';
import ReactRedux from 'react-redux';
import tabActions from 'Product/Components/ProductList/ActionCreators/tabActions';
import Tabs from 'Product/Components/ProductList/Components/Tabs/Tabs';

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

export default ReactRedux.connect(mapStateToProps, mapDispatchToProps)(Tabs);