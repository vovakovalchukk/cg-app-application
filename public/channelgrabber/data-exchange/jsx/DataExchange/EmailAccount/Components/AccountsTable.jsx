import React from "react";
import styled from "styled-components";
import RemoveIcon from 'Common/Components/RemoveIcon';
import EmailAddressInputComponent from "./EmailAddressInput";

const TYPE_FROM = 'from';
const TYPE_TO = 'to';

const AccountsTableContainer = styled.div`
    width: 600px;
`;
const TableContainer = styled.table`
    margin-bottom: 20px;
`;
const TableCellContainer = styled.td`
    text-align: left;
    display: flex;
    align-items: center;
`;

class EmailAccountsTable extends React.Component {
    static defaultProps = {
        accounts: {},
        type: TYPE_TO
    };

    isTypeFrom = () => {
        return this.props.type.toString().trim() === TYPE_FROM;
    };

    renderTableHeader = () => {
        return <tr>
            <th>
                {"Send " + this.props.type + " Email Address"}
            </th>
        </tr>;
    };

    renderAccountRows = () => {
        const isTypeFrom = this.isTypeFrom();

        return Object.keys(this.props.accounts).map(id => {
            let account = this.props.accounts[id];
            return this.renderAccountRow(account);
        });
    };

    renderAccountRow = (account) => {
        return <tr>
            <TableCellContainer>
                {this.renderEmailAddressInput(account)}
                {this.renderRemoveColumn(account)}
            </TableCellContainer>
        </tr>
    };

    renderEmailAddressInput = (account) => {
        return <EmailAddressInputComponent
            type="email"
            value={account.newAddress}
            placeholder="Enter an email address here"
            name={this.props.type + '.' + account.id}
            isVerifiable={this.isTypeFrom()}
            verifiedStatus={''}
            onChange={this.updateEmailAddress.bind(this, account.id)}
        />
    };

    updateEmailAddress(accountId, newAddress) {
        this.props.actions.changeEmailAddress(accountId, newAddress);
    };

    renderRemoveColumn = (account) => {
        return <RemoveIcon
            className={'remove-icon-new'}
        />;
    };

    render() {
        return <AccountsTableContainer>
            <form name={this.props.type + "EmailAccounts"}>
                <TableContainer>
                    <thead>
                        {this.renderTableHeader()}
                    </thead>
                    <tbody>
                        {this.renderAccountRows()}
                    </tbody>
                </TableContainer>
            </form>
        </AccountsTableContainer>;
    }
}

export default EmailAccountsTable;
export {TYPE_FROM as EmailAccountTypeFrom, TYPE_TO as EmailAccountTypeTo};
