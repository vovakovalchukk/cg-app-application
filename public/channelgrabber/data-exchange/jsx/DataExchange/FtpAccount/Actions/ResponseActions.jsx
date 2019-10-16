export default {
    accountDeletedSuccessfully: (index) => {
        return {
            type: "ACCOUNT_DELETED_SUCCESSFULLY",
            payload: {
                index
            }
        }
    },
    accountDeleteFailed: (index, message) => {
        n.error(message);
        return {
            type: "ACCOUNT_DELETE_FAILED",
            payload: {
                index,
                message
            }
        }
    },
    accountUpdatedSuccessfully: (index, account, response) => {
        return {
            type: "ACCOUNT_UPDATED_SUCCESSFULLY",
            payload: {
                index,
                account,
                response
            }
        }
    },
};
