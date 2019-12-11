import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import App from 'MessageCentre/App';
import Combined from 'MessageCentre/Actions/Combined';
import combineActions from "../../products/jsx/Product/Components/ProductList/ActionCreators/combineActions";

"use strict";

const mapStateToProps = function(state) {
    return {
        filters: state.filters,

    };
};

const mapDispatchToProps = function(dispatch, ownProps) {
    let combinedActionCreators = Combined(ownProps);
    return {
        actions: bindActionCreators(
            combinedActionCreators,
            dispatch
        )
    };
};


export default connect(mapStateToProps, mapDispatchToProps)(App);