import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import Select from 'Common/Components/Select';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class IncludePurchaseOrdersInAvailableCell extends React.Component {

    changeSetting = (e) => {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        this.props.actions.updateIncPOStockInAvailable(row.id, e.value);
    };

    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rows,
            stock,
            incPOStockInAvailableOptions
        } = this.props;
        let rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);

        if (isParentProduct) {
            return <span></span>
        }

        let selected = stock.incPOStockInAvailable.byProductId[rowData.id];
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
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                />
            </div>
        );
    }
}

export default IncludePurchaseOrdersInAvailableCell;