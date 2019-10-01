import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import EmailAccountsTable, {EmailAccountTypeFrom, EmailAccountTypeTo} from "./AccountsTable";
import Actions from "../Actions/Actions";

class EmailAccountsComponent extends React.Component {
    static defaultProps = {
        accounts: {}
    };

    renderAccountsTableForType = (type) => {
        let accounts = this.filterAccountsByType(type);
        return <EmailAccountsTable
            accounts={accounts}
            type={type}
            actions={this.props.actions}
        />;
    };

    filterAccountsByType = (type) => {
        let accountsForType = {};
        Object.keys(this.props.accounts).forEach(accountId => {
            let account = this.props.accounts[accountId];
            if (account.type.toString().trim() === type) {
                accountsForType[accountId] = account;
            }
        });
        return accountsForType;
    };

    render() {
        return (<div>
            {this.renderAccountsTableForType(EmailAccountTypeFrom)}
            {this.renderAccountsTableForType(EmailAccountTypeTo)}
        </div>);
    }
}

const mapStateToProps = function(state) {
    return {
        accounts: state.emailAccounts
    }
};

const mapDispatchToProps = function(dispatch) {
    return {
        actions: bindActionCreators(
            Actions,
            dispatch
        ),
        actionTest: Actions
    };
};

const EmailAccountsConnector = connect(mapStateToProps, mapDispatchToProps);

export default EmailAccountsConnector(EmailAccountsComponent);
