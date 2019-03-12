import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';

class OnPurchaseOrderCell extends React.Component {
    render() {
        const {
            products,
            rowIndex
        } = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);

        if (isParentProduct) {
            return <span></span>
        }

        let onPurchaseOrderValue = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );

        return (
            <span className={this.props.className + " onPurchaseOrder-cell"}>
                {onPurchaseOrderValue}
            </span>
        );
    }
}

export default OnPurchaseOrderCell;