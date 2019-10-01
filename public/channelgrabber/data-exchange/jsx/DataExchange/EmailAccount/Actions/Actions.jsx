import ResponseActions from "./ResponseActions";

export default {
    changeEmailAddress: (id, newAddress) => {
        return {
            type: "CHANGE_EMAIL_ADDRESS",
            payload: {
                id,
                newAddress
            }
        }
    },
    removeEmailAddress: (account) => {
        return async function(dispatch) {
            n.notice('Your email address ' + account.address + ' is being deleted..', 2000);
            let response = await deleteAccountAjax(account.id);

            if (response.success === true) {
                dispatch(ResponseActions.accountDeletedSuccessfully(account.id));
                return;
            }

            dispatch(
                ResponseActions.accountDeleteFailed(
                    account.id,
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
