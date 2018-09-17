define([], function() {
    "use strict";
    
    let tabActions = (function() {
        return {
            changeTab: (desiredTabKey) => {
                return function(dispatch, getState) {
                    let state = getState();
                    let numberOfVisibleFixedColumns = getState.customGetters.getVisibleFixedColumns(state).length;
                    dispatch({
                        type: "TAB_CHANGE",
                        payload: {
                            desiredTabKey,
                            numberOfVisibleFixedColumns
                        }
                    });
                }
            },
        };
    })();
    
    return tabActions;
});