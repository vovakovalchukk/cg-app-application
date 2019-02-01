import React from 'react';
import stateUtility from "../stateUtility";
import LowStockInputs from "../Components/LowStockInputs";
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import {StockModeCellContainer, StyledSafeSubmits} from "./StockMode";
import portalFactory from "../Portal/portalFactory";

class LowStockCell extends React.Component {
    static defaultProps = {};

    render() {
        const product = stateUtility.getRowData(this.props.products, this.props.rowIndex);

        if (stateUtility.isParentProduct(product) || !product.stock) {
            return null;
        }

        return <StockModeCellContainer>
            {this.renderLowStockInputs(product, this.getPortalSettingsForDropdown())}
            {this.renderSubmits()}
        </StockModeCellContainer>
    }

    renderLowStockInputs = (product, portalSettingsForDropdown) => {
        return <LowStockInputs
            product={product}
            portalSettingsForDropdown={portalSettingsForDropdown}
            lowStockThreshold={stateUtility.getLowStockThresholdForProduct(product, this.props.stock)}
            default={this.getLowStockThresholdDefaults()}
            actions={this.props.actions}
        />
    };

    renderSubmits = () => {
        let portalSettings = this.getPortalSettingsForSubmits();

        if (!portalSettings) {
            return null;
        }

        return portalFactory.createPortal({
            portalSettings: portalSettings,
            Component: StyledSafeSubmits,
            componentProps: {}
        });
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
        return portalSettingsFactory.createPortalSettings({
            elemType: type,
            rowIndex: this.props.rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: this.props.distanceFromLeftSideOfTableToStartOfCell,
            width: this.props.width,
            allRows: this.props.rows.allIds
        });
    };
}

export default LowStockCell;