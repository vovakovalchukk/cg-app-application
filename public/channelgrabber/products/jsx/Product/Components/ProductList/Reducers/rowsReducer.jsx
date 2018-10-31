import utility from "../utility";
import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    firstCellHasBeenRendered:false
};

var rowsReducer = reducerCreator(initialState, {
    "ROWS_REORDER_BY_ROW_INDEX": function(state, action) {
        let allRows = document.querySelectorAll('.js-row');
        var rowArr = [].slice.call(allRows).sort((a, b) => {
            //todo - change this to check the classNames
            let aRowIndex = utility.getRowIndexFromRow(a);
            let bRowIndex = utility.getRowIndexFromRow(b);
            // sorting from bottom to top so that we can get the submits to overlap rows via their
            // parent element's z-index
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