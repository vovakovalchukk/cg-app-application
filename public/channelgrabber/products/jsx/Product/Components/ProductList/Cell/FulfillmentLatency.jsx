import React from "react";
import SafeInputStateless from 'Common/Components/SafeInputStateless';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import stateUtility from 'Product/Components/ProductList/stateUtility';

class FulfillmentLatencyCell extends React.Component {
    getUniqueInputId = () => {
        return this.props.rowData.id + '-' + this.props.columnKey
    };

    render() {
        let rowData = this.props.rowData;

        if (!stateUtility.isSimpleProduct(rowData) && !stateUtility.isVariation(rowData)) {
            return (<span></span>);
        }

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex: this.props.rowIndex,
            distanceFromLeftSideOfTableToStartOfCell: this.props.distanceFromLeftSideOfTableToStartOfCell,
            width: this.props.width,
            allRows: this.props.rows.allIds
        });

        return (
            <span className={this.props.className}>
                <SafeInputStateless
                    name='fulfillmentLatency'
                    key={this.getUniqueInputId()}
                    setIsEditing={false}
                    submitsPortalSettings={portalSettings}
                    width={45}
                    shouldRenderSubmits={false}
                />
            </span>
        );
    }
}

export default FulfillmentLatencyCell