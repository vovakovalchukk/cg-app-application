import stateUtility from "../stateUtility";
import React from "react";
import columnKeys from 'Product/Components/ProductList/Column/columnKeys';

class CellWrapper extends React.Component {
    render() {
        let {CellContent, products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);

        if(this.isFirstRenderedCell()){
            // have to do this because fixed-data-tables re-renders rows in a non sequential order
            this.props.actions.reOrderRowsByRowIndex();
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
    isFirstRenderedCell() {
        return this.props.rowIndex === 0 && this.props.columnKey === Object.keys(columnKeys)[0];
    }
    isLastRow() {
        return this.props.products.visibleRows.length === this.props.rowIndex;
    }
}

export default CellWrapper;