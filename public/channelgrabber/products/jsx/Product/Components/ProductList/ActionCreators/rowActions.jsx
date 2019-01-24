import React from "react";

let rowActions = (function() {
    return {
        runIntialUpdateForRowsIfApplicable: () => {
            console.log('in runInitialUpdateForRowsIfApplicable');
            return function updateRowsThunk(dispatch, getState) {
                if(getState().rows.initialModifyHasOccurred){
                    console.log('not running...');


                    return;
                }
                console.log('RUNNING!');

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