export default {
    changeEmailAddress: (id, newAddress) => {
        return {
            type: "CHANGE_EMAIL_ADDRESS",
            payload: {
                id,
                newAddress
            }
        }
    }
};
