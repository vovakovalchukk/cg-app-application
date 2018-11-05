import stateUtility from "../stateUtility";
import React from "react";
import columnKeys from 'Product/Components/ProductList/Column/columnKeys';

class CellWrapper extends React.Component {
    render() {
        let {CellContent, products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);

        if(this.shouldReorderRows()){
            // have to do this because fixed-data-tables re-renders rows in a non sequential order.
            // if performance issues are hit later move this out into a higher component
            // todo - reinstate this after finding out the cause of the UI jank when entering stockMode levels
            this.props.actions.modifyZIndexOfRows();
        }

        if(this.isLastRow()){
            return (
                <span></span>
            )
        }
        return (
            <CellContent
                {...this.props}
                rowData={rowData}
            />
        )
    };
    shouldReorderRows() {
        // modulus 12 so that we will generally see a re-order for any visible set of rows
        if((this.props.rowIndex % 12) === 0 && this.props.columnKey === Object.keys(columnKeys)[0]){
            let rowsExist = !!document.querySelectorAll('.js-row').length;
            if(rowsExist){
                return true;
            }
        }
    }
    isLastRow() {
        return this.props.products.visibleRows.length === this.props.rowIndex;
    }
}

export default CellWrapper;