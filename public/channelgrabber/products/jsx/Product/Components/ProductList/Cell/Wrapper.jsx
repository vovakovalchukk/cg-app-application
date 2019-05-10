import stateUtility from "../stateUtility";
import React from "react";
import ReactDOM from "react-dom";
import columnKeys from 'Product/Components/ProductList/Column/columnKeys';

let rowData = {};

let cellRefs = {};
let cellNodes = {};

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

        let cellIdentifier = this.setCellIdentifier();

        return (
            <CellContent
                {...this.props}
                rowData={rowData[rowIndex]}
                cellNode = {cellNodes[cellIdentifier]}
            />
        )
    };
    setCellIdentifier() {
        if(!this.props.products.visibleRows.length){
            return;
        }
        let cellIdentifier = stateUtility.getCellIdentifier(
            this.props.products,
            this.props.rowIndex,
            this.props.columnKey
        );
        cellNodes[cellIdentifier] = ReactDOM.findDOMNode(this);
        return cellIdentifier;
    }
    isFirstCell() {
        return this.props.rowIndex === 0 && (this.props.columnKey === columnKeys.productExpand);
    }
    isLastRow() {
        return this.props.products.visibleRows.length === this.props.rowIndex;
    }
}

export default CellWrapper;