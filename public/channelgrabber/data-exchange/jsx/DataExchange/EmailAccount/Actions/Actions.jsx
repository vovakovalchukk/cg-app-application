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
        }
    },
    addNewEmailAccount: (type, account) => {
        return {
            type: "ADD_NEW_EMAIL_ACCOUNT",
            payload: {
                type,
                account
            }
        }
    },
    removeEmailAddress: (type, index, account) => {
        return async function(dispatch) {
            if (account.id === null) {
                dispatch(ResponseActions.accountDeletedSuccessfully(type, index));
                return;
            }

            n.notice('Your email address ' + account.address + ' is being deleted..', 2000);
            let response = await deleteAccountAjax(account.id);

            if (response.success === true) {
                n.success('The email address ' + account.newAddress + ' was deleted successfully');
                dispatch(ResponseActions.accountDeletedSuccessfully(type, index));
                return;
            }

            dispatch(
                ResponseActions.accountDeleteFailed(
                    type,
                    index,
                    'There was an error while deleting your email address. Please try again or contact support if the problem persists.'
                )
            );
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
