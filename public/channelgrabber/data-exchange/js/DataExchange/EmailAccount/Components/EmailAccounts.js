import React from 'react';
import { connect } from 'react-redux';

class EmailAccountsComponent extends React.Component {
    render() {
        return 'TADA rendered!';
    }
}

let mapStateToProps = function (state) {
    return {
        accounts: state.emailAccounts
    };
};

let mapDispatchToProps = function (dispatch) {
    return {};
};

let EmailAccountsConnector = connect(mapStateToProps, mapDispatchToProps);

export default EmailAccountsConnector(EmailAccountsComponent);
