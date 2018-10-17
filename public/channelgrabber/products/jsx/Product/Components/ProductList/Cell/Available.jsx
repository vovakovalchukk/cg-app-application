import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import elementTypes from 'Product/Components/ProductList/PortalSettings/elementTypes';
import portalSettingsFactory from 'Product/Components/ProductList/PortalSettings/factory'

class AvailableCell extends React.Component {
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
        let availableValue = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
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