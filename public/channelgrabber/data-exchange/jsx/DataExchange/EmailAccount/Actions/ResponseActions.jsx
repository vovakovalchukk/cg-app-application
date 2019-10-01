export default {
    accountDeletedSuccessfully: (id) => {
        return {
            type: "ACCOUNT_DELETED_SUCCESSFULLY",
            payload: {
                id
            }
        }
    },
    accountDeleteFailed: (id, message) => {
        n.error(message);
        return {
            type: "ACCOUNT_DELETE_FAILED",
            payload: {
                id,
                message
            }
        }
    }
};
