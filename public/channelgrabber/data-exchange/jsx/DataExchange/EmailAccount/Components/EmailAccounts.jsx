import React from 'react';
import {connect} from 'react-redux';
import EmailAccountsTable, {EmailAccountTypeFrom, EmailAccountTypeTo} from "./AccountsTable";

class EmailAccountsComponent extends React.Component {
    static defaultProps = {
        accounts: []
    };

    renderAccountsTableForType = (type) => {
        let accounts = this.filterAccountsByType(type);
        return <EmailAccountsTable
            accounts={accounts}
            type={type}
        />;
    };

    filterAccountsByType = (type) => {
        return this.props.accounts.filter(account => {
            return account.type == type;
        });
    };

    render() {
        return (<div>
            {this.renderAccountsTableForType(EmailAccountTypeFrom)}
            {this.renderAccountsTableForType(EmailAccountTypeTo)}
        </div>);
    }
}

let mapStateToProps = function(state) {
    return {
        accounts: state.emailAccounts
    }
};

let mapDispatchToProps = function (dispatch) {
    return {};
};

let EmailAccountsConnector = connect(mapStateToProps, mapDispatchToProps);

export default EmailAccountsConnector(EmailAccountsComponent);
