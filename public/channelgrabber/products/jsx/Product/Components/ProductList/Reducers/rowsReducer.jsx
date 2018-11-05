import utility from "../utility";
import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    firstCellHasBeenRendered: false
};

var rowsReducer = reducerCreator(initialState, {
    "MODIFY_ZINDEX_OF_ROWS": function(state, action) {
        let allRows = document.querySelectorAll('.js-row');

        let orderArray = [];

        for (let i = 0; i < allRows.length; i++) {
            orderArray[i] = allRows.length-i-1;
        }

        let rowsContainer, parentRow;


        for(let j=0; j<allRows.length; j++){
            let rowIndex = utility.getRowIndexFromRow(allRows[j]);
            parentRow = allRows[j].parentNode;
            if (j === 0) {
                rowsContainer = parentRow.parentNode;
            }
             console.log('in loop',{
                j, rowIndex
            });

             parentRow.style.zIndex = (allRows.length*2)-rowIndex;
        }


//        for (let i = 0; i < allRows.length; i++) {
//
//            let rowIndex = utility.getRowIndexFromRow(allRows[i]);
//            parentRow = allRows[i].parentNode;
//
//            if (i === 0) {
//                rowsContainer = parentRow.parentNode;
//            }
//
//            console.log('in loop',{
//                i, rowIndex, parentRow,
//                'allRows[i]': allRows[i]
//            });
//
//            //todo invert this so it stacks from bottom to top
//            if(rowIndex===i){
//                console.log('appending child');
//
//
//                rowsContainer.appendChild(parentRow)
//            }
//            break;
////            parentRows.push(allRows[i].parentNode);
//        }

        console.log('rowsContainer after order', rowsContainer);



//
//
//        for (let i = 0; i < newOrder.length; i++) {
//
//            for (let j = 0; j < newOrder.length; j++) {
//
//                if (i === newOrders[j]) {
//
//                }
//
//            }
//        }
//
//        console.log('parentRows: ', parentRows);
//
//        let rowsContainer = parentRows[0].parentNode;
//
//        console.log('rowsContainer: ', rowsContainer);
//
//        parentRows.forEach(function(row) {
//            rowsContainer.appendChild(row);
//        });
        return state;
    }
});

export default rowsReducer