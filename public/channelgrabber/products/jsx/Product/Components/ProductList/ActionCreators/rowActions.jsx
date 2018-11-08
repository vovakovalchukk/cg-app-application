import React from "react";

let rowActions = (function() {
    return {
        runIntialUpdateForRowsIfApplicable:() => {
            return function(dispatch, getState){
                if(!getState().rows.initialModifyHasOccurred){
                    console.log('updating');
                    dispatch(rowActions.updateRowsForPortals());
                }else{
                    console.log('not updating....');
                    
                    
                }
            }
        },
        updateRowsForPortals: () =>{
            return function(dispatch){
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