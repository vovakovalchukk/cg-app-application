import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import tabActions from 'Product/Components/ProductList/ActionCreators/tabActions';
import Tabs from 'Product/Components/ProductList/Components/Tabs/Tabs';

const mapStateToProps = function(state) {
    return {
        tabs: state.tabs
    };
};

const mapDispatchToProps = function(dispatch) {
    return {
        actions: bindActionCreators(tabActions, dispatch)
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(Tabs);