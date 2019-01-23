import utility from "../utility";
import reducerCreator from 'Common/Reducers/creator';
import visibleRowService from 'Product/Components/ProductList/VisibleRow/service';

"use strict";

var initialState = {
    initialModifyHasOccurred: false,
    allIds: []
};

var rowsReducer = reducerCreator(initialState, {
    "MODIFY_ZINDEX_OF_ROWS": function(state) {
        visibleRowService.modifyZIndexOfRows();
        return Object.assign({}, state, {
            initialModifyHasOccurred: true
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