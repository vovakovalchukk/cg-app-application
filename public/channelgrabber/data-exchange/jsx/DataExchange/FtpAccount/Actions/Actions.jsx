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
};
