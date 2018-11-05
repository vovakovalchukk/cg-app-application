import React from "react";

let rowActions = (function() {
    return {
        modifyZIndexOfRows: () => {
            return {
                type: "MODIFY_ZINDEX_OF_ROWS",
                payload: {}
            };
        }
    };
}());

export default rowActions;