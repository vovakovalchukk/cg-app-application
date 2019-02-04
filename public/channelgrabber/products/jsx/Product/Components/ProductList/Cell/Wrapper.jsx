import stateUtility from "../stateUtility";
import React from "react";
import columnKeys from 'Product/Components/ProductList/Column/columnKeys';

class CellWrapper extends React.Component {
    render() {
        let {CellContent, products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);

        if (this.isFirstCell()) {
            this.props.actions.runIntialUpdateForRowsIfApplicable();
        }

        if (this.isLastRow()) {
            return (
                <span/>
            )
        }
        return (
            <CellContent
                {...this.props}
                rowData={rowData}
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