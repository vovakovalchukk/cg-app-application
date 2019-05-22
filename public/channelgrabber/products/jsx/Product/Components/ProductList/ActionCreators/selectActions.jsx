"use strict";

let selectActions = (function() {
    return {
        removeActiveSelect: () => {
            return {
                type: "REMOVE_ACTIVE_SELECT"
            }
        },
        selectActiveToggle : (columnKey, productId, index) => {
            return {
                type: "SELECT_ACTIVE_TOGGLE",
                payload: {
                    columnKey,
                    productId,
                    index
                }
            }
        },
    };
})();

export default selectActions;