import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes';
import portalSettingsFactory from 'Product/Components/ProductList/Portal/settingsFactory'
import visibleRowService from "../VisibleRow/service";

class AvailableCell extends React.Component {
    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            visibleRows
        } = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);

        if (isParentProduct) {
            return <span></span>
        }

//        if(!this.props.rows.initialModifyHasOccurred){
//            this.props.actions.updateRowsForPortals();
//        }

        let availableValue = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            // Had to hardcode to show the safe input buttons in the right place
            distanceFromLeftSideOfTableToStartOfCell: distanceFromLeftSideOfTableToStartOfCell + 13,
            width,
            allRows : this.props.rows.allIds
        });

        return (
            <span className={this.props.className + " available-cell"}>
                <Input
                    name='available'
                    initialValue={parseFloat(availableValue)}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, rowData)}
                    inputClassNames={'u-width-100pc u-text-align-right'}
                    sku={rowData.sku}
                    submitsPortalSettings={portalSettings}
                />
            </span>
        );
    }

    onChange(e) {
        const {products, rowIndex} = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateAvailable(rowData, 'available', e.target.value);
    };
}

export default AvailableCell;