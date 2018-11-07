import utility from "../utility";
import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    firstRowHasBeenRendered: false,
    initialModifyHasOccurred: false,
    allIds: [],
    scrollTimeout: () => {}
};

var rowsReducer = reducerCreator(initialState, {
    "MARK_FIRST_ROW_AS_RENDERED": function(state){
        return Object.assign({}, state, {
            firstRowHasBeenRendered: true
        });
    },
    "MODIFY_ZINDEX_OF_ROWS": function(state) {
        modifyZIndexOfScrollableRows();
        modifyZIndexOfHeader();
        return Object.assign({}, state, {
            initialModifyHasOccurred: true
        });
    },
    "VISIBLE_ROWS_RECORD": function(state) {
        let allVisibleRowsIds = utility.getArrayOfAllRenderedRows().sort();
        return Object.assign({}, state, {
            allIds: allVisibleRowsIds
        });
    }
});

export default rowsReducer

function modifyZIndexOfScrollableRows() {
    let allRows = document.querySelectorAll('.js-row');
    let rowsContainer, parentRow;

    for (let j = 0; j < allRows.length; j++) {
        let rowIndex = utility.getRowIndexFromRow(allRows[j]);
        parentRow = allRows[j].parentNode;
        if (j === 0) {
            rowsContainer = parentRow.parentNode;
        }
        parentRow.style.zIndex = (allRows.length * 2) - rowIndex;
    }
}

function modifyZIndexOfHeader() {
    let headerParent = document.querySelector('.public_fixedDataTable_header').parentNode;
    headerParent.style.zIndex = 110;
}