export default {
    accountDeletedSuccessfully: (type, index) => {
        return {
            type: "ACCOUNT_DELETED_SUCCESSFULLY",
            payload: {
                type,
                index
            }
        }
    },
    accountDeleteFailed: (type, index, message) => {
        n.error(message);
        return {
            type: "ACCOUNT_DELETE_FAILED",
            payload: {
                type,
                index,
                message
            }
        }
    },
    accountSavedSuccessfully: (type, index, account) => {
        return {
            type: "ACCOUNT_SAVED_SUCCESSFULLY",
            payload: {
                type,
                index,
                account
            }
        }
    },
    accountSaveFailed: () => {}
};
