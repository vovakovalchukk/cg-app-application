import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Select from 'Common/Components/Select';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class IncludePurchaseOrdersInAvailableCell extends React.Component {

    changeSetting = (e) => {
        // TODO
        // const {products, rowIndex, stock} = this.props;
        // const row = stateUtility.getRowData(products, rowIndex);
        // this.props.actions.updateVat(row.id, countryCode, e.value);
    };

    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rows,
            incPOStockInAvailableOptions
        } = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);

        if (isParentProduct) {
            return <span></span>
        }

        let selected = (rowData.stock.includePurchaseOrdersUseDefault ? 'default' : (rowData.stock.includePurchaseOrders ? 'on' : 'off'));
        let selectedOption = incPOStockInAvailableOptions.find((option) => {
            return option.value == selected;
        });

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: rows.allIds
        });

        return (
            <div className={this.props.className + " includePurchaseOrdersInAvailable-cell"}>
                <Select
                    options={incPOStockInAvailableOptions}
                    selectedOption={selectedOption}
                    onOptionChange={this.changeSetting}
                    //classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                />
            </div>
        );
    }
}

export default IncludePurchaseOrdersInAvailableCell;