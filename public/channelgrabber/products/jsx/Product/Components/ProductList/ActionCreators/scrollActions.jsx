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
        },
        updateHorizontalScrollIndex: (index) => {
            return {
                type: "HORIZONTAL_SCROLLBAR_INDEX_UPDATE",
                payload: {
                    index
                }
            }
        }
    };
}());

export default scrollActions;