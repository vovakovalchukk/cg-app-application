import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import App from 'MessageCentre/App';
import Combined from 'MessageCentre/Actions/Combined';

'use strict';

const mapStateToProps = function(state) {
    return {
        status: state.status,
        threads: state.threads,
        messages: state.messages,
        column: state.column,
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