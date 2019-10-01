import React from "react";
import styled from "styled-components";
import EmailAddressInputComponent from "./EmailAddressInput";
import RemoveIcon from 'Common/Components/RemoveIcon';

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
        accounts: [],
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

        let accounts = this.props.accounts.map(account => {
            return this.renderAccountRow(account);
        });

        return accounts;
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
            value={account.address}
            placeholder="Enter an email address here"
            name={this.props.type + '.' + (account.id ? account.id : 'new')}
            isVerifiable={this.isTypeFrom()}
            verifiedStatus={''}
        />
    };

    addEmptyTableRow = () => {
        return <tr>
            <td>{this.renderAddressField({address: "", id: null})}</td>
            {this.isTypeFrom() ? <td></td> : null}
            <td></td>
        </tr>;
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
