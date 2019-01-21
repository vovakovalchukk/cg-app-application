import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes';
import portalSettingsFactory from 'Product/Components/ProductList/Portal/settingsFactory'

class AllocatedCell extends React.Component {
    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        } = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);

        if (isParentProduct) {
            return <span></span>
        }

        let allocatedValue = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );

        return (
            <span className={this.props.className + " allocated-cell"}>
                {allocatedValue}
            </span>
        );
    }
}

export default AllocatedCell;