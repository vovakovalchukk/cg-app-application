import React from "react";

let rowActions = (function() {
    return {
        reOrderRowsByRowIndex: () => {
            return {
                type: "ROWS_REORDER_BY_ROW_INDEX",
                payload: {}
            };
        }
    };
}());

export default rowActions;