import utility from "../utility";

let visibleRowService = (function() {

    function modifyZIndexOfHeader() {
        let headerParent = document.querySelector('.public_fixedDataTable_header').parentNode;
        headerParent.style.zIndex = 110;
    }

    let service = {
        modifyZIndexOfRows: () => {
            modifyZIndexOfHeader();
            let allRows = document.querySelectorAll('.js-row');
            let rowsContainer, parentRow;

            let visibleRowsIndexes = utility.getArrayOfAllRenderedRowIndexes();
            let highestRowIndexOfVisibleRows = Math.max.apply(null, visibleRowsIndexes);

            for (let index = 0; index < allRows.length; index++) {
                let rowIndex = utility.getRowIndexFromRow(allRows[index]);

                parentRow = allRows[index].parentNode;
                if (index === 0) {
                    rowsContainer = parentRow.parentNode;
                }

                let desiredZIndex = highestRowIndexOfVisibleRows - rowIndex ;

                parentRow.style.zIndex = desiredZIndex;
            }
        }
    };

    return service;
}());

export default visibleRowService;