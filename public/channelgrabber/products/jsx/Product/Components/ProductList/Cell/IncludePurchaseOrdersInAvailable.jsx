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
    getVatSelectActive() {
        const rowData = this.props.rowData;
        let isActiveOnState = stateUtility.isCurrentActiveSelect(rowData, this.props.select, this.props.columnKey)
        if (!isActiveOnState || this.props.scroll.userScrolling || !this.props.rows.initialModifyHasOccurred) {
            return false;
        }
        return true;
    };
    selectToggle(productId) {
        this.props.actions.selectActiveToggle(this.props.columnKey, productId);
    };
    render() {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            rows,
            stock,
            incPOStockInAvailableOptions,
            rowData
        } = this.props;

        const isParentProduct = stateUtility.isParentProduct(rowData);

        let productIncPoStockInAvailable = stock.incPOStockInAvailable.byProductId[rowData.id];
        if (isParentProduct || !productIncPoStockInAvailable) {
            return <span/>;
        }

        let selected = productIncPoStockInAvailable.selected;

        let selectedOption = incPOStockInAvailableOptions.find((option) => {
            return option.value == selected;
        });

        let containerElement = this.props.cellNode;

        let portalSettingsParams = {
            elemType: elementTypes.INCLUDE_PURCHASE_ORDERS_IN_AVAILABLE_SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds,
            containerElement
        };

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings(portalSettingsParams);

        return (
            <div className={this.props.className + " includePurchaseOrdersInAvailable-cell"}>
                <StatelessSelect
                    options={incPOStockInAvailableOptions}
                    selectedOption={selectedOption}
                    onOptionChange={this.props.actions.updateIncPOStockInAvailable.bind(this, rowData.id)}
                    classNames={'u-width-140px'}
                    portalSettingsForDropdown={portalSettingsForDropdown}
                    selectToggle={this.selectToggle.bind(this, rowData.id)}
                    active={this.getVatSelectActive()}
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