import constants from "./Config/constants";

let utility = (function() {
    return {
        findDifferenceOfTwoArrays: (arr1, arr2) => {
            return arr1.filter(x => !arr2.includes(x));
        },
        getRowIndexFromRow(visibleRow) {
            let lastVisibleRowClasses = visibleRow.className;
            let classArray = lastVisibleRowClasses.split(' ');

            let rowClass = classArray.find(classStr => classStr.indexOf('js-row-') > -1);

            let rowClassSplitByHyphens = rowClass.split('-');
            let rowClassIndex = parseInt(rowClassSplitByHyphens[rowClassSplitByHyphens.length - 1]);
            return rowClassIndex;
        },
        getArrayOfAllRenderedRows() {
            let allVisibleNonHeaderRows = getAllVisibleRowNodes();
            let allRows = allVisibleNonHeaderRows.map(row => {
                let rowIndex = utility.getRowIndexFromRow(row);
                return rowIndex
            });
            return allRows;
        }
    };
}());

export default utility;

function getAllVisibleRowNodes() {
    let rows = document.getElementsByClassName(constants.ROW_CLASS_PREFIX);
    let rowNodes = [];
    for (var i = 0; i < rows.length; i++) {
        //todo --- keep an eye on this bit and see if it's necessary before submitting
        if (i === rows.length) {
            continue;
        }
        rowNodes.push(rows[i]);
    }

    return rowNodes;
}