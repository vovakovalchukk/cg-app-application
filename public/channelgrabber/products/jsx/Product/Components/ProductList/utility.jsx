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
        getArrayOfAllRenderedRowIndexes() {
            let allVisibleNonHeaderRows = getAllVisibleRowNodes();
            let allRows = allVisibleNonHeaderRows.map(row => {
                let rowIndex = utility.getRowIndexFromRow(row);
                return rowIndex
            });
            return allRows;
        },
        shortenNameForCell(name){
            let cutOffLength = 70;
            if(name.length > cutOffLength){
                return name.substring(0, cutOffLength) + "...";
            }
            return name;
        }
    };
}());

export default utility;

function getAllVisibleRowNodes() {
    let rows = document.getElementsByClassName(constants.ROW_CLASS_PREFIX);
    let rowNodes = [];
    for (var index = 0; index < rows.length; index++) {
        if (index === rows.length) {
            continue;
        }
        rowNodes.push(rows[index]);
    }

    return rowNodes;
}