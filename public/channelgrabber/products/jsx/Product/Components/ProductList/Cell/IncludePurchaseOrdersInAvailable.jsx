import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility.jsx';
import StatelessSelect from 'Common/Components/Select--stateless';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class IncludePurchaseOrdersInAvailableCell extends React.Component {
    static defaultProps = {
        products : {},
        rowIndex: '',
        distanceFromLeftSideOfTableToStartOfCell: '',
        width: '',
        actions: {},
        rows: {},
        stock: {},
        incPOStockInAvailableOptions: {}
    };
    getVatSelectActive(activePropOnState) {
        if (!activePropOnState || this.props.scroll.userScrolling || !this.props.rows.initialModifyHasOccurred) {
            return false;
        }
        return true;
    };
    selectToggle(productId) {
        this.props.actions.toggleIncPOStockInAvailableSelect(productId);
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

        const rowData = stateUtility.getRowData(products, rowIndex);
        const isParentProduct = stateUtility.isParentProduct(rowData);

        let productIncPoStockInAvailable = stock.incPOStockInAvailable.byProductId[rowData.id];
        if (isParentProduct || !productIncPoStockInAvailable) {
            return <span/>;
        }

        let selected = productIncPoStockInAvailable.selected;

        let selectedOption = incPOStockInAvailableOptions.find((option) => {
            return option.value == selected;
        });

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INCLUDE_PURCHASE_ORDERS_IN_AVAILABLE_SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: rows.allIds
        });

        return (
            <div className={this.props.className + " includePurchaseOrdersInAvailable-cell"}>
                <StatelessSelect
                    options={incPOStockInAvailableOptions}
                    selectedOption={selectedOption}
                    onOptionChange={this.props.actions.updateIncPOStockInAvailable.bind(this, rowData.id)}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    selectToggle={this.selectToggle.bind(this, rowData.id)}
                    active={this.getVatSelectActive(productIncPoStockInAvailable.active)}
                    styleVars={{
                        widthOfInput: 110,
                        widthOfDropdown: 130
                    }}
                />
            </div>
        );
    }
}

export default IncludePurchaseOrdersInAvailableCell;