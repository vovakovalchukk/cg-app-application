import utility from "../utility";

let visibleRowService = (function() {

    function modifyZIndexOfHeader(){
        let headerParent = document.querySelector('.public_fixedDataTable_header').parentNode;
        headerParent.style.zIndex = 110;
    }

    let service = {
        modifyZIndexOfRows: () => {
            modifyZIndexOfHeader();
            let allRows = document.querySelectorAll('.js-row');
            let rowsContainer, parentRow;

            for (let j = 0; j < allRows.length; j++) {
                let rowIndex = utility.getRowIndexFromRow(allRows[j]);
                parentRow = allRows[j].parentNode;
                if (j === 0) {
                    rowsContainer = parentRow.parentNode;
                }
                let desiredZIndex = (allRows.length * 2) - rowIndex;
                //todo - put flag in here to stop uneccessary z-index
                parentRow.style.zIndex = desiredZIndex;
            }
        },
    };

    return service;
}());

export default visibleRowService;