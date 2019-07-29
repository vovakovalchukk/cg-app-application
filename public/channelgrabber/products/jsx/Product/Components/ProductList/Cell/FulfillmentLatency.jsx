import React from "react";
import SafeInputStateless from 'Common/Components/SafeInputStateless';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import stateUtility from 'Product/Components/ProductList/stateUtility';

class FulfillmentLatencyCell extends React.Component {
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

        let isEditing = false;
        if (rowData.id in this.props.detail['fulfillmentLatency'].byProductId) {
            isEditing = this.props.detail['fulfillmentLatency'].byProductId[rowData.id].isEditing
        }

        return (
            <span className={this.props.className}>
                <SafeInputStateless
                    name='fulfillmentLatency'
                    step='1'
                    key={this.getUniqueInputId()}
                    submitCallback={this.saveDetail}
                    cancelInput={this.cancelInput}
                    setIsEditing={this.setIsEditing}
                    onValueChange={this.changeDetailValue}
                    value={this.getValue()}
                    submitsPortalSettings={portalSettings}
                    width={45}
                    isEditing={isEditing}
                    shouldRenderSubmits={this.shouldRenderSubmits}
                />
            </span>
        );
    }

    getUniqueInputId = () => {
        return this.props.rowData.id + '-' + this.props.columnKey
    };

    saveDetail = () => {
        this.props.actions.saveDetail(this.props.rowData, 'fulfillmentLatency');
    };

    cancelInput = () => {
        this.props.actions.cancelInput(this.props.rowData, 'fulfillmentLatency');
    };

    setIsEditing = (isEditing) => {
        this.props.actions.setIsEditing(this.props.rowData.id, 'fulfillmentLatency', isEditing);
    };

    changeDetailValue = (event) => {
        this.props.actions.changeDetailValue(this.props.rowData.id, 'fulfillmentLatency', event);
    };

    getValue = () => {
        let rowData = this.props.rowData;

        if (rowData.id in this.props.detail['fulfillmentLatency'].byProductId) {
            let fulfillmentLatency = this.props.detail['fulfillmentLatency'].byProductId[rowData.id];
            if (typeof fulfillmentLatency.valueEdited === 'string') {
                return fulfillmentLatency.valueEdited;
            }
            if (typeof fulfillmentLatency.value === 'string') {
                return fulfillmentLatency.value;
            }
        }

        if ('fulfillmentLatency' in rowData.details) {
            return rowData.details['fulfillmentLatency'] || '';
        }

        return '';
    };

    shouldRenderSubmits = () => {
        return !this.props.scroll.userScrolling;
    };
}

export default FulfillmentLatencyCell