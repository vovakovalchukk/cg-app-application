import React from "react";

let rowActions = (function() {
    return {
        runIntialUpdateForRowsIfApplicable: () => {
            return function updateRowsThunk(dispatch, getState) {
                if (getState().rows.initialModifyHasOccurred || !document.querySelector('.public_fixedDataTable_header')){
                    return;
                }
                dispatch(rowActions.updateRowsForPortals());
            }
        },
        updateRowsForPortals: () => {
            return function(dispatch) {
                dispatch(rowActions.modifyZIndexOfRows());
                dispatch(rowActions.recordVisibleRows());
            }
        },
        modifyZIndexOfRows: () => {
            return {
                type: "MODIFY_ZINDEX_OF_ROWS"
            };
        },
        recordVisibleRows: () => {
            return {
                type: "VISIBLE_ROWS_RECORD"
            }
        }
    };
}());

export default rowActions;