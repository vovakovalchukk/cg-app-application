import React from "react";
import EmailValidator from "email-validator";
import RemoveIcon from 'Common/Components/RemoveIcon';
import PopupComponent from 'Common/Components/Popup';
import EmailAddressInputComponent from "Common/Components/EmailAddressInput";

const TYPE_FROM = 'from';
const TYPE_TO = 'to';

class AccountsTable extends React.Component {
    static defaultProps = {
        accounts: [],
        type: TYPE_TO
    };

    static SAVE_TIMEOUT_DURATION = 5000;

    saveTimeoutIds = {};

    componentDidMount() {
        this.addNewEmailAccount();
        window.addEventListener('beforeunload', this.beforeunload.bind(this));
    };

    componentWillUnmount() {
        window.removeEventListener('beforeunload', this.beforeunload.bind(this));
    }

    beforeunload(e) {
        if (Object.keys(this.saveTimeoutIds).length === 0) {
            return false;
        }

         e.preventDefault();
         e.returnValue = true;
    }

    isTypeFrom = () => {
        return this.props.type.toString().trim() === TYPE_FROM;
    };

    isLastAccount = (index) => {
        return index === this.props.accounts.length -1;
    };

    isAddressChanged = (account) => {
        return account.address.toString().trim() !== account.newAddress.toString().trim();
    };

    isEmailAddressValid = (email) => {
        return EmailValidator.validate(email);
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
            <td className={'u-flex-v-center u-text-align-left'}>
                {this.renderEmailAddressInput(account, index)}
                {this.renderRemoveColumn(account, index)}
            </td>
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

        const mergedAccount = this.buildAccountForSaving(account, index, newAddress);

        if (!this.isAddressChanged(mergedAccount)) {
            return;
        }

        if (!this.isEmailAddressValid(mergedAccount.newAddress)) {
            return;
        }

        let timeoutId = window.setTimeout(async (index, mergedAccount) => {
            await this.props.actions.saveEmailAddress(this.props.type, index, mergedAccount);
            this.clearTimeoutForAccountSave(index);
        }, AccountsTable.SAVE_TIMEOUT_DURATION, index, mergedAccount);

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

        const mergedAccount = this.buildAccountForSaving(account, index, account.newAddress);

        if (!this.isAddressChanged(mergedAccount)) {
            return;
        }

        if (!this.isEmailAddressValid(mergedAccount.newAddress)) {
            return;
        }

        this.props.actions.saveEmailAddress(this.props.type, index, mergedAccount);
    };

    buildAccountForSaving = (account, index, newAddress) => {
        return Object.assign({}, account, {newAddress: newAddress}, {
            verified: false,
            verificationStatus: null
        });
    };

    async verifyEmailAddress(account, index) {
        let accountId = account.id;
        if (this.isAddressChanged(account)) {
            this.clearTimeoutForAccountSave(index);
            let response = await this.props.actions.saveEmailAddress(this.props.type, index, account);
            accountId = response.id;
        }
        await this.props.actions.verifyEmailAddress(this.props.type, index, accountId);
        window.triggerEvent('triggerPopup');
    };

    renderRemoveColumn = (account, index) => {
        if (this.isLastAccount(index)) {
            return null;
        }

        return <span className={'u-cursor-pointer'}>
            <RemoveIcon
                className={'remove-icon-new'}
                onClick={() => {this.removeEmailAddress(index, account)}}
            />
        </span>;
    };

    removeEmailAddress = (index, account) => {
        this.clearTimeoutForAccountSave(index);
        this.props.actions.removeEmailAddress(this.props.type, index, account);
    };

    renderConfirmationPopup = () => {
        if (!this.isTypeFrom()) {
            return null;
        }

        return <PopupComponent
                headerText='Confirmation'
                yesButtonText='Ok'
                renderNoButton={false}
        >
                <strong>Excellent!</strong><br/>
                <p>Now we just need you to confirm your email address.</p>
                <p>Please check your inbox for a confirmation email from Amazon Web Services.</p>
        </PopupComponent>;
    };

    render() {
        return <div className={"u-form-width-medium"}>
            <form name={this.props.type + "EmailAccounts"}>
                <table className={'u-margin-bottom-med'}>
                    <thead>
                        {this.renderTableHeader()}
                    </thead>
                    <tbody>
                        {this.renderAccountRows()}
                    </tbody>
                </table>
            </form>
            {this.renderConfirmationPopup()}
        </div>;
    }
}

export default AccountsTable;
export {TYPE_FROM as EmailAccountTypeFrom, TYPE_TO as EmailAccountTypeTo};
