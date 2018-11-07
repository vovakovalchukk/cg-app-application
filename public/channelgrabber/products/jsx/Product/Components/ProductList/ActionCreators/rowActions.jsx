import React from "react";

let rowActions = (function() {
    return {
        signalFirstCellAsRendered: () => {
            return function(dispatch, getState) {
                let state = getState();
                if (!state.rows.initialModifyHasOccurred) {
                    dispatch(rowActions.modifyZIndexOfRows());
                }
            }
        },
//        scrollVertical: () => {
//            return function(dispatch, getState) {
//                let scrollTimeout;
//                window.clearTimeout(scrollTimeout);
//                scrollTimeout = setTimeout(function() {
////                    console.log('scrolling has stopped...');
//                    dispatch(rowActions.modifyZIndexOfRows());
//                    dispatch(rowActions.recordVisibleRows());
//                }, 500);
//            }
//        },
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