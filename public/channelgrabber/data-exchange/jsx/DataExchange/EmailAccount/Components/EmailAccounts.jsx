import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import styled from "styled-components";
import AccountsTable, {EmailAccountTypeFrom, EmailAccountTypeTo} from "./AccountsTable";
import Actions from "../Actions/Actions";

const Container = styled.div`
    margin-top:45px;
`;

class EmailAccountsComponent extends React.Component {
    static defaultProps = {
        accounts: {}
    };

    renderAccountsTableForType = (type) => {
        let accounts = this.props.accounts[type];
        return <AccountsTable
            accounts={accounts}
            type={type}
            actions={this.props.actions}
        />;
    };

    render() {
        return (<Container>
            {this.renderAccountsTableForType(EmailAccountTypeFrom)}
            {this.renderAccountsTableForType(EmailAccountTypeTo)}
        </Container>);
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
        )
    };
};

const EmailAccountsConnector = connect(mapStateToProps, mapDispatchToProps);

export default EmailAccountsConnector(EmailAccountsComponent);
