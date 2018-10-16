import utility from "../utility";
import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {};

var rowsReducer = reducerCreator(initialState, {
    "ROWS_REORDER_BY_ROW_INDEX": function(state, action) {
        let allRows = document.querySelectorAll('.js-row');
        var rowArr = [].slice.call(allRows).sort((a, b) => {
            //todo - change this to check the classNames
            let aRowIndex = utility.getRowIndexFromRow(a);
            let bRowIndex = utility.getRowIndexFromRow(b);
            return aRowIndex < bRowIndex ? 1 : -1;
        });
        let parentRows = rowArr.map(row => {
            return row.parentNode;
        });
        let rowsContainer = parentRows[0].parentNode;
        parentRows.forEach(function(row) {
            rowsContainer.appendChild(row);
        });
        return state;
    }
});

export default rowsReducer