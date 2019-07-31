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
        if (this.props.account) {
            let account = this.props.account;
            isEditing = this.lookupValue(
                this.props.detail['fulfillmentLatency'].byAccountId,
                [account.id, rowData.id, 'isEditing'],
                isEditing
            );
        } else {
            isEditing = this.lookupValue(
                this.props.detail['fulfillmentLatency'].byProductId,
                [rowData.id, 'isEditing'],
                isEditing
            );
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
                    placeholder={this.getPlaceholder()}
                    submitsPortalSettings={portalSettings}
                    width={45}
                    isEditing={isEditing}
                    shouldRenderSubmits={this.shouldRenderSubmits}
                />
            </span>
        );
    }

    lookupValue = (prop, ids, fallback) => {
        let noMatch = ids.some(id => {
            if (!(id in prop)) {
                return true;
            }
            prop = prop[id];
        });

        return noMatch ? fallback : prop || fallback;
    };

    getUniqueInputId = () => {
        let inputId = this.props.rowData.id + '-' + this.props.columnKey;
        if (this.props.account) {
            inputId += '-' + this.props.account.id;
        }
        return inputId;
    };

    saveDetail = () => {
        this.props.actions.saveDetail(this.props.rowData, 'fulfillmentLatency', {
            accountId: this.props.account ? this.props.account.id : null
        });
    };

    cancelInput = () => {
        this.props.actions.cancelInput(this.props.rowData, 'fulfillmentLatency', {
            accountId: this.props.account ? this.props.account.id : null
        });
    };

    setIsEditing = (isEditing) => {
        this.props.actions.setIsEditing(this.props.rowData.id, 'fulfillmentLatency', isEditing, {
            accountId: this.props.account ? this.props.account.id : null
        });
    };

    changeDetailValue = (event) => {
        this.props.actions.changeDetailValue(this.props.rowData.id, 'fulfillmentLatency', event, {
            accountId: this.props.account ? this.props.account.id : null
        });
    };

    getValue = () => {
        let rowData = this.props.rowData;

        let fulfillmentLatency;
        if (this.props.account) {
            let account = this.props.account;
            fulfillmentLatency = this.lookupValue(
                this.props.detail['fulfillmentLatency'].byAccountId,
                [account.id, rowData.id],
                {'value': rowData.details['fulfillmentLatency-' + account.id] || ''}
            );
        } else {
            fulfillmentLatency = this.lookupValue(
                this.props.detail['fulfillmentLatency'].byProductId,
                [rowData.id],
                {'value': rowData.details['fulfillmentLatency'] || ''}
            );
        }

        if (typeof fulfillmentLatency.valueEdited === 'string') {
            return fulfillmentLatency.valueEdited;
        }

        if (typeof fulfillmentLatency.value === 'string') {
            return fulfillmentLatency.value;
        }

        return '';
    };

    getPlaceholder = () => {
        return this.props.account ? this.props.account.externalData.fulfillmentLatency : '';
    };

    shouldRenderSubmits = () => {
        return !this.props.scroll.userScrolling;
    };
}

export default FulfillmentLatencyCell