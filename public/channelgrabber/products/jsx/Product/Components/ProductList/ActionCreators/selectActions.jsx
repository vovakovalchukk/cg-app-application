"use strict";

let selectActions = (function() {
    return {
        selectActiveToggle : (columnKey, productId) => {
            return {
                type: "SELECT_ACTIVE_TOGGLE",
                payload: {
                    columnKey,
                    productId
                }
            }
        },
    };
})();

export default selectActions;