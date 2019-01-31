import React from 'react';
import stateUtility from "../stateUtility";
import LowStockInputs from "../Components/LowStockInputs";
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";

class LowStockCell extends React.Component {
    static defaultProps = {};

    getLowStockThresholdDefaults() {
        return {
            toggle: this.props.userSettings.lowStockThresholdToggle,
            value: this.props.userSettings.lowStockThresholdValue
        };
    };

    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        } = this.props;

        let portalSettingsForDropdown = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.LOW_STOCK_SELECT_DROPDOWN,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        const product = stateUtility.getRowData(products, rowIndex);

        if (stateUtility.isParentProduct(product)) {
            return null;
        }

        return <LowStockInputs
            product={product}
            portalSettingsForDropdown={portalSettingsForDropdown}
            lowStockThreshold={stateUtility.getLowStockThresholdForProduct(product, this.props.stock)}
            default={this.getLowStockThresholdDefaults()}
        />;
    }
}

export default LowStockCell;