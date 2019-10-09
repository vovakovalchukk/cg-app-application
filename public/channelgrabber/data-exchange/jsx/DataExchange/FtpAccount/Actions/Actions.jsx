import ResponseActions from "./ResponseActions";

export default {
    updateInputValue: (index, property, newValue) => {
        return {
            type: "UPDATE_INPUT_VALUE",
            payload: {
                index,
                property,
                newValue
            }
        }
    },
    addNewAccount: (account) => {
        return {
            type: "ADD_NEW_ACCOUNT",
            payload: {
                account
            }
        }
    },
    removeAccount: (index, account) => {
        return async function(dispatch) {
            if (!account.id) {
                dispatch(ResponseActions.accountDeletedSuccessfully(index));
                return
            }

            n.notice('Deleting account...', 2000);
            let response = await deleteAccountAjax(account.id);

            if (response.success === true) {
                n.success('The account was deleted successfully');
                dispatch(ResponseActions.accountDeletedSuccessfully(index));
                return;
            }

            dispatch(
                ResponseActions.accountDeleteFailed(
                    index,
                    'There was an error while deleting your FTP account. Please try again or contact support if the problem persists.'
                )
            );
        };
    },
    saveAccount: (index, account) => {
        return async function(dispatch) {
            let response = await saveAccountAjax(account);

            if (response.success === true) {
                n.success('The account was updated successfully.');
                dispatch(ResponseActions.accountUpdatedSuccessfully(index, account, response));
                return;
            }

            let message = response.message? response.message : 'There was an error while saving your FTP account. Please try again or contact support if the problem persists.';
            n.error(message);
        };
    },
    testFtpAccount: (index, account) => {
        return async function(dispatch) {
            n.notice('Please wait while we test the connection to your server: ' + account.server + ':' + account.port);
            let response = await testFtpAccountAjax(account.id);

            if (response.success === true) {
                n.success('Connection successful!');
                return;
            }

            n.error('Couldn\'t connect to the FTP server. Please update the connection details and try again');
        }
    }
};

const deleteAccountAjax = async (id) => {
    return $.ajax({
        context: this,
        url: '/dataExchange/accounts/ftp/remove',
        type: 'POST',
        data: {
            id
        },
        success: function (response) {
            return response;
        },
        error: function (error) {
            return error;
        }
    });
};

const saveAccountAjax = async (account) => {
    let postData = Object.assign({}, account);
    Object.keys(postData).forEach((key) => {
        let value = postData[key];
        value === null || value === '' ? delete postData[key] : null;
    });

    return $.ajax({
        context: this,
        url: '/dataExchange/accounts/ftp/save',
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

const testFtpAccountAjax = async (id) => {
    return $.ajax({
        context: this,
        url: '/dataExchange/accounts/ftp/test',
        type: 'POST',
        data: {
            id
        },
        success: function (response) {
            return response;
        },
        error: function (error) {
            return error;
        }
    });
};