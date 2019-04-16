import stateUtility from "../stateUtility";
import React from "react";
import columnKeys from 'Product/Components/ProductList/Column/columnKeys';

let rowData = {};

let cellRefs = {};

class CellWrapper extends React.Component {
    render() {
        let {CellContent, products, rowIndex} = this.props;
        rowData[rowIndex] = stateUtility.getRowData(products, rowIndex);

        if (this.isFirstCell()) {
            this.props.actions.runIntialUpdateForRowsIfApplicable();
        }

        if (this.isLastRow()) {
            return (
                <span/>
            )
        }

        console.log('in cellWrapper with this.props', this.props);


        let cellRef = stateUtility.getCellRef(
            this.props.products,
            this.props.rowIndex,
            this.props.columnKey
        );

        return (
            <CellContent
                {...this.props}
                rowData={rowData[rowIndex]}
                ref={(component) => cellRefs[cellRef] = component}
                cellRef = {cellRefs[cellRef]}
            />
        )
    };
    isFirstCell() {
        return this.props.rowIndex === 0 && (this.props.columnKey === columnKeys.productExpand);
    }
    isLastRow() {
        return this.props.products.visibleRows.length === this.props.rowIndex;
    }
}

export default CellWrapper;