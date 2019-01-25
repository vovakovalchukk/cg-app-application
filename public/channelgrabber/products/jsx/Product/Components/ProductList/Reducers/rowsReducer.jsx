import utility from "../utility";
import reducerCreator from 'Common/Reducers/creator';
import visibleRowService from 'Product/Components/ProductList/VisibleRow/service';

"use strict";

var initialState = {
    initialModifyHasOccurred: false,
    allIds: []
};

var rowsReducer = reducerCreator(initialState, {
    "VIEW_CHANGE": function(state){
        return Object.assign({}, state, {
            initialModifyHasOccurred: false
        });
    },
    "MODIFY_ZINDEX_OF_ROWS": function(state) {
        let hasModified = visibleRowService.modifyZIndexOfRows();
        let initialModifyHasOccurred = state.initialModifyHasOccurred ? true : hasModified;
        return Object.assign({}, state, {
            initialModifyHasOccurred
        });
    },
    "VISIBLE_ROWS_RECORD": function(state) {
        let allVisibleRowsIds = utility.getArrayOfAllRenderedRowIndexes().sort();
        state.allIds = allVisibleRowsIds;
        return Object.assign({}, state, {
            allIds: allVisibleRowsIds
        });
    }
});

export default rowsReducer