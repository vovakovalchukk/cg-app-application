"use strict";

let selectActions = (function() {
    return {
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