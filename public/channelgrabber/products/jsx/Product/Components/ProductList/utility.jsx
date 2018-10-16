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
        }
    };
}());

export default utility;