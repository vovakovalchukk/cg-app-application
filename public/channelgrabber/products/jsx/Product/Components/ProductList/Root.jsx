import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import ProductList from 'Product/Components/ProductList/ProductList'
import combineActions from 'Product/Components/ProductList/ActionCreators/combineActions'

"use strict";

const mapStateToProps = function(state) {
    return {
        products: state.products,
        tabs: state.tabs,
        list: state.list,
        pagination: state.pagination,
        accounts: state.accounts.getAccounts(state),
        columns: state.columns,
        stock: state.stock,
        vat: state.vat,
        bulkSelect: state.bulkSelect,
        rows: state.rows,
        userSettings: state.userSettings,
        search: state.search,
        scroll: state.scroll,
        pickLocations: state.pickLocations
    };
};

const mapDispatchToProps = function(dispatch, ownProps) {
    let combinedActionCreators = combineActions(ownProps);
    return {
        actions: bindActionCreators(
            combinedActionCreators,
            dispatch
        )
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ProductList);