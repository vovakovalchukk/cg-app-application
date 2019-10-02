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
const RemoveIconContainer = styled.span`
    cursor: pointer;
`;

class EmailAccountsTable extends React.Component {
    static defaultProps = {
        accounts: [],
        type: TYPE_TO
    };

    isTypeFrom = () => {
        return this.props.type.toString().trim() === TYPE_FROM;
    };

    isLastAccount = (index) => {
        return index === this.props.accounts.length -1;
    };

    renderTableHeader = () => {
        return <tr>
            <th>
                {"Send " + this.props.type + " Email Address"}
            </th>
        </tr>;
    };

    renderAccountRows = () => {
        return this.props.accounts.map((account, index) => {
            return this.renderAccountRow(account, index);
        });
    };

    renderAccountRow = (account, index) => {
        return <tr>
            <TableCellContainer>
                {this.renderEmailAddressInput(account, index)}
                {this.renderRemoveColumn(account, index)}
            </TableCellContainer>
        </tr>
    };

    renderEmailAddressInput = (account, index) => {
        return <EmailAddressInputComponent
            type="email"
            value={account.newAddress}
            placeholder="Enter an email address here"
            name={this.props.type + '.' + index}
            isVerifiable={this.isTypeFrom()}
            verifiedStatus={account.verifiedStatus}
            onChange={this.updateEmailAddress.bind(this, index)}
        />
    };

    updateEmailAddress(index, newAddress) {
        this.props.actions.changeEmailAddress(this.props.type, index, newAddress);
        if (this.isLastAccount(index) && newAddress !== '') {
            this.props.actions.addNewEmailAccount(this.props.type, {
                type: this.props.type,
                id: null,
                verifiedStatus: null,
                verified: false,
                address: '',
                newAddress: ''
            });
        }
    };

    renderRemoveColumn = (account, index) => {
        return <RemoveIconContainer>
            <RemoveIcon
                className={'remove-icon-new'}
                onClick={this.props.actions.removeEmailAddress.bind(this, this.props.type, index, account)}
            />
        </RemoveIconContainer>;
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
