import ResponseActions from "./ResponseActions";

export default {
    changeEmailAddress: (type, index, newAddress) => {
        return {
            type: "CHANGE_EMAIL_ADDRESS",
            payload: {
                type,
                index,
                newAddress
            }
        };
    },
    addNewEmailAccount: (type, account) => {
        return {
            type: "ADD_NEW_EMAIL_ACCOUNT",
            payload: {
                type,
                account
            }
        };
    },
    removeEmailAddress: (type, index, account) => {
        return async function (dispatch) {
            if (account.id === null) {
                dispatch(ResponseActions.accountDeletedSuccessfully(type, index));
                return;
            }

            let response = await deleteAccountAjax(account.id);

            if (response.success === true) {
                n.success('The email address ' + account.newAddress + ' was deleted successfully');
                dispatch(ResponseActions.accountDeletedSuccessfully(type, index));
                return;
            }

            dispatch(ResponseActions.accountDeleteFailed(type, index, 'There was an error while deleting your email address. Please try again or contact support if the problem persists.'));
        };
    },
    saveEmailAddress: (type, index, account) => {
        return async function (dispatch) {
            let response = await saveAccountAjax(account);

            if (response.success !== true) {
                let message = response.message ? response.message : 'There was an error while saving your email address. Please contact support if the problem persists';
                n.error(message);
                dispatch(ResponseActions.accountSaveFailed(type, index, account, message));
                return;
            }

            let updatedAccount = Object.assign({}, account);
            !!response.etag ? updatedAccount.etag = response.etag : false;

            n.success('The email address ' + account.address + ' was successfully saved.');
            dispatch(ResponseActions.accountSavedSuccessfully(type, index, updatedAccount));
        };
    },
    verifyEmailAddress: (type, index, account) => {
        return async function (dispatch) {
            let response = await verifyEmailAjax(account.id);

            if (response.success !== true) {
                // fail
            }

            dispatch(ResponseActions.accountVerificationUpdate(type, index, response.verificationStatus));
        };
    }
};

const deleteAccountAjax = async function (id) {
    return $.ajax({
        context: this,
        url: '/dataExchange/accounts/email/remove',
        type: 'POST',
        data: {
            id: id
        },
        success: function (response) {
            return response;
        },
        error: function (error) {
            return error;
        }
    });
};

const saveAccountAjax = async function (account) {
    let postData = {
        type: account.type,
        address: account.newAddress
    };

    if (account.id) {
        postData.id = account.id;
    }

    if (account.etag) {
        postData.etag = account.etag;
    }

    return $.ajax({
        context: this,
        url: '/dataExchange/accounts/email/save',
        type: 'POST',
        data: postData,
        success: function (response) {
            return response;
        },
        error: function (error) {
            return error;
        }
    });
};

const verifyEmailAjax = async function (id) {
    return $.ajax({
        context: this,
        url: '/dataExchange/accounts/email/verify',
        type: 'POST',
        data: {
            id: id
        },
        success: function (response) {
            return response;
        },
        error: function (error) {
            return error;
        }
    });
};
