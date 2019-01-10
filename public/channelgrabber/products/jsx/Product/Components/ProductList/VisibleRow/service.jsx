import utility from "../utility";

let visibleRowService = (function() {

    function modifyZIndexOfHeader() {
        let headerParent = document.querySelector('.public_fixedDataTable_header').parentNode;
        headerParent.style.zIndex = 110;
    }

    let service = {
        modifyZIndexOfRows: () => {
            console.log('triggering the modify-zindex of rows');
            
            
            modifyZIndexOfHeader();
            let allRows = document.querySelectorAll('.js-row');
            let rowsContainer, parentRow;

            for (let index = 0; index < allRows.length; index++) {
                let amountOfVisibleRows = utility.getArrayOfAllRenderedRows().length;
                let rowIndex = utility.getRowIndexFromRow(allRows[index]);

                parentRow = allRows[index].parentNode;
                if (index === 0) {
                    rowsContainer = parentRow.parentNode;
                }

                let desiredZIndex = rowIndex % amountOfVisibleRows;

                parentRow.style.zIndex = desiredZIndex;
            }
        }
    };

    return service;
}());

export default visibleRowService;