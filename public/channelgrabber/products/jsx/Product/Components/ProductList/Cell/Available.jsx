import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Input from 'Common/Components/SafeInput';
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes';
import portalSettingsFactory from 'Product/Components/ProductList/Portal/settingsFactory'

class AvailableCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        rows: {},
        rowData: {},
    };
    getUniqueInputId = () => {
        return this.props.rowData.id+'-'+ this.props.columnKey
    };
    render() {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rowData
        } = this.props;

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
            // Had to hardcode to show the safe input buttons in the right place
            distanceFromLeftSideOfTableToStartOfCell: distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        return (
            <span className={this.props.className + " available-cell"}>
                <Input
                    name='available'
                    key={this.getUniqueInputId()}
                    initialValue={parseFloat(availableValue)}
                    step="0.1"
                    submitCallback={this.props.actions.updateAvailable.bind(this, rowData)}
                    inputClassNames={'u-width-100pc'}
                    sku={rowData.sku}
                    submitsPortalSettings={portalSettings}
                    width={45}
                />
            </span>
        );
    }
}

export default AvailableCell;