let scrollActions = (function() {
    return {
        setUserScrolling: () => {
            return {
                type: "SET_USER_SCROLLING"
            }
        },
        unsetUserScrolling: () => {
            return {
                type: "UNSET_USER_SCROLLING"
            }
        }
    };
}());

export default scrollActions;