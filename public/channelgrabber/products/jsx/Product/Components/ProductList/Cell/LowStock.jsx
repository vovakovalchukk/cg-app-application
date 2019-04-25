import React from 'react';
import stateUtility from "../stateUtility";
import LowStockInputs from "../Components/LowStockInputs";
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import {StockModeCellContainer} from "./StockMode";

class LowStockCell extends React.Component {
    static defaultProps = {
        cellNode: null
    };

    renderLowStockInputs = (product, portalSettingsForDropdown, getPortalSettingsForSubmits) => {
        return <LowStockInputs
            product={product}
            portalSettingsForDropdown={portalSettingsForDropdown}
            getPortalSettingsForSubmits={getPortalSettingsForSubmits}
            lowStockThreshold={stateUtility.getLowStockThresholdForProduct(product, this.props.stock)}
            default={this.getLowStockThresholdDefaults()}
            actions={this.props.actions}
            select={this.props.select}
            scroll={this.props.scroll}
            rows={this.props.rows}
            cellNode={this.props.cellNode}
        />
    };

    getLowStockThresholdDefaults = () =>  {
        return {
            toggle: this.props.userSettings.lowStockThresholdToggle,
            value: this.props.userSettings.lowStockThresholdValue
        };
    };

    getPortalSettingsForDropdown = () => {
        return this.getPortalSettingsForType(elementTypes.LOW_STOCK_SELECT_DROPDOWN);
    };

    getPortalSettingsForSubmits = () => {
        return this.getPortalSettingsForType(elementTypes.INPUT_SAFE_SUBMITS);
    };

    getPortalSettingsForType = (type) => {
        let containerElement = this.props.cellNode;

        return portalSettingsFactory.createPortalSettings({
            elemType: type,
            rowIndex: this.props.rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: this.props.distanceFromLeftSideOfTableToStartOfCell,
            width: this.props.width,
            allRows: this.props.rows.allIds,
            containerElement
        });
    };

    render() {
        const product = stateUtility.getRowData(this.props.products, this.props.rowIndex);

        if (stateUtility.isParentProduct(product) || !product.stock) {
            return null;
        }

        return <StockModeCellContainer>
            {this.renderLowStockInputs(product, this.getPortalSettingsForDropdown(), this.getPortalSettingsForSubmits())}
        </StockModeCellContainer>
    }
}

export default LowStockCell;