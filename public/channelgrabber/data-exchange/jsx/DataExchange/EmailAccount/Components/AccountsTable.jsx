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

    static SAVE_TIMEOUT_DURATION = 5000;

    saveTimeoutIds = {};

    componentDidMount() {
        this.addNewEmailAccount();
    };

    isTypeFrom = () => {
        return this.props.type.toString().trim() === TYPE_FROM;
    };

    isLastAccount = (index) => {
        return index === this.props.accounts.length -1;
    };

    isAddressChanged = (account) => {
        return account.address.toString().trim() !== account.newAddress.toString().trim();
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
            verificationStatus={this.isAddressChanged(account) ? null : account.verificationStatus}
            onChange={this.updateEmailAddress.bind(this, account, index)}
            onKeyPressEnter={this.onKeyPressEnter.bind(this, account, index)}
            onVerifyClick={this.verifyEmailAddress.bind(this, account, index)}
        />
    };

    updateEmailAddress = (account, index, newAddress) => {
        this.props.actions.changeEmailAddress(this.props.type, index, newAddress);

        if (this.isLastAccount(index)) {
            this.addNewEmailAccount(index);
        }

        this.handleSaveAccount(account, index, newAddress);
    };

    addNewEmailAccount = () => {
        this.props.actions.addNewEmailAccount(this.props.type, {
            type: this.props.type,
            id: null,
            verificationStatus: null,
            verified: false,
            address: '',
            newAddress: ''
        });
    };

    handleSaveAccount = (account, index, newAddress) => {
        this.clearTimeoutForAccountSave(index);

        account = Object.assign(account, {newAddress: newAddress});
        if (!this.isAddressChanged(account)) {
            return;
        }

        let timeoutId = window.setTimeout((index, account) => {
            this.props.actions.saveEmailAddress(this.props.type, index, account);
        }, EmailAccountsTable.SAVE_TIMEOUT_DURATION, index, account);

        this.saveTimeoutIds = Object.assign(this.saveTimeoutIds, {
            [index]: timeoutId
        });
    };

    clearTimeoutForAccountSave = (index) => {
        if (!this.saveTimeoutIds[index]) {
            return;
        }

        window.clearTimeout(this.saveTimeoutIds[index]);
        delete this.saveTimeoutIds[index];
    };

    onKeyPressEnter = (account, index) => {
        this.clearTimeoutForAccountSave(index);
        this.props.actions.saveEmailAddress(this.props.type, index, account);
    };

    async verifyEmailAddress(account, index) {
        let accountId = account.id;
        if (this.isAddressChanged(account)) {
            this.clearTimeoutForAccountSave(index);
            let response = await this.props.actions.saveEmailAddress(this.props.type, index, account);
            accountId = response.id;
        }
        return this.props.actions.verifyEmailAddress(this.props.type, index, accountId);
    };

    renderRemoveColumn = (account, index) => {
        if (this.isLastAccount(index)) {
            return null;
        }

        return <RemoveIconContainer>
            <RemoveIcon
                className={'remove-icon-new'}
                onClick={this.removeEmailAddress.bind(this, index, account)}
            />
        </RemoveIconContainer>;
    };

    removeEmailAddress = (index, account) => {
        this.clearTimeoutForAccountSave(index);
        this.props.actions.removeEmailAddress(this.props.type, index, account);
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
