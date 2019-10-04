import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import Actions from "../Actions/Actions";

class FtpAccountsComponent extends React.Component {
    static defaultProps = {
        accounts: {},
        accountTypeOptions: {},
        defaultPorts: {}
    };

    render() {
        return null;
    }
}

const mapStateToProps = function(state) {
    return {
        accounts: state.ftpAccounts
    }
};

const mapDispatchToProps = function(dispatch) {
    return {
        actions: bindActionCreators(
            Actions,
            dispatch
        )
    };
};

const FtpAccountsConnector = connect(mapStateToProps, mapDispatchToProps);

export default FtpAccountsConnector(FtpAccountsComponent);
